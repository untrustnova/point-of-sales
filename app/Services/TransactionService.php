<?php

namespace App\Services;

use App\Events\NewSaleTransactionEvent;
use App\Exceptions\InvalidTransactionException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        public StockService $stockService
    ) {}

    /**
     * Memproses transaksi penjualan kasir baru.
     *
     * @param array{
     *     store_id: int,
     *     user_id: int,
     *     cashier_shift_id?: int|null,
     *     customer_id?: int|null,
     *     status?: string,
     *     discount_amount?: float,
     *     tax_amount?: float,
     *     paid_amount: float,
     *     payment_method: string,
     *     notes?: string|null,
     *     items: array<int, array{
     *         product_id: int,
     *         product_variant_id?: int|null,
     *         quantity: int,
     *         sell_price: float,
     *         discount_amount?: float
     *     }>,
     *     payments?: array<int, array{
     *         payment_method: string,
     *         amount: float,
     *         reference_number?: string|null
     *     }>
     * } $data
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $storeId = $data['store_id'];
            $status = $data['status'] ?? 'completed';

            // Hitung subtotal dan item
            $subtotal = 0;
            $processedItems = [];

            foreach ($data['items'] as $itemData) {
                $product = Product::where('store_id', $storeId)->findOrFail($itemData['product_id']);
                $variant = null;

                if (! empty($itemData['product_variant_id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->findOrFail($itemData['product_variant_id']);
                }

                $quantity = (int) $itemData['quantity'];
                $sellPrice = (float) $itemData['sell_price'];
                $buyPrice = $variant ? ($variant->buy_price ?? $product->buy_price) : $product->buy_price;
                $itemDiscount = (float) ($itemData['discount_amount'] ?? 0);
                $itemSubtotal = ($sellPrice * $quantity) - $itemDiscount;

                $subtotal += $itemSubtotal;

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name.($variant ? " ({$variant->name})" : ''),
                    'unit_name' => $product->unit?->short_name ?? 'pcs',
                    'quantity' => $quantity,
                    'buy_price' => $buyPrice,
                    'sell_price' => $sellPrice,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = ($subtotal - $discountAmount) + $taxAmount;
            $paidAmount = (float) ($data['paid_amount'] ?? $totalAmount);
            $changeAmount = max(0, $paidAmount - $totalAmount);

            $invoiceNumber = $this->generateInvoiceNumber($storeId);

            $transaction = Transaction::create([
                'store_id' => $storeId,
                'cashier_shift_id' => $data['cashier_shift_id'] ?? null,
                'user_id' => $data['user_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'transaction_date' => Carbon::now(),
                'status' => $status,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($processedItems as $itemData) {
                $transaction->items()->create($itemData);

                // Potong stok jika status transaksi langsung completed
                if ($status === 'completed') {
                    $this->stockService->recordMovement(
                        storeId: $storeId,
                        productId: $itemData['product_id'],
                        type: 'sale',
                        quantity: $itemData['quantity'],
                        variantId: $itemData['product_variant_id'],
                        userId: $data['user_id'],
                        referenceType: Transaction::class,
                        referenceId: $transaction->id,
                        notes: "Penjualan invoice: {$transaction->invoice_number}"
                    );
                }
            }

            // Catat rincian pembayaran
            if (! empty($data['payments'])) {
                foreach ($data['payments'] as $pmt) {
                    $transaction->payments()->create([
                        'payment_method' => $pmt['payment_method'],
                        'amount' => $pmt['amount'],
                        'reference_number' => $pmt['reference_number'] ?? null,
                    ]);
                }
            } else {
                $transaction->payments()->create([
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'amount' => $paidAmount,
                ]);
            }

            // Tambahkan piutang ke customer jika pembayaran dengan kredit/debt
            if ($transaction->payment_method === 'debt' && ! empty($transaction->customer_id)) {
                $customer = Customer::find($transaction->customer_id);
                if ($customer) {
                    $customer->increment('debt_amount', $totalAmount - $paidAmount);
                }
            }

            Event::dispatch(new NewSaleTransactionEvent($transaction));

            return $transaction;
        });
    }

    /**
     * Membatalkan (Void) transaksi dan mengembalikan stok.
     */
    public function voidTransaction(int $transactionId, int $userId, string $reason): Transaction
    {
        return DB::transaction(function () use ($transactionId, $userId, $reason) {
            $transaction = Transaction::with('items')->lockForUpdate()->findOrFail($transactionId);

            if ($transaction->status !== 'completed') {
                throw new InvalidTransactionException('Hanya transaksi dengan status selesai yang dapat dibatalkan.');
            }

            $transaction->update([
                'status' => 'cancelled',
                'notes' => trim(($transaction->notes ?? '')." [VOID by User #{$userId}: {$reason}]"),
            ]);

            // Mengembalikan stok produk yang pernah dipotong
            foreach ($transaction->items as $item) {
                if ($item->product_id) {
                    $this->stockService->recordMovement(
                        storeId: $transaction->store_id,
                        productId: $item->product_id,
                        type: 'return',
                        quantity: $item->quantity,
                        variantId: $item->product_variant_id,
                        userId: $userId,
                        referenceType: Transaction::class,
                        referenceId: $transaction->id,
                        notes: "Void transaksi invoice: {$transaction->invoice_number}"
                    );
                }
            }

            return $transaction;
        });
    }

    public function generateInvoiceNumber(int $storeId): string
    {
        $dateStr = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $countToday = Transaction::where('store_id', $storeId)
            ->whereDate('created_at', Carbon::today())
            ->count() + 1;

        return sprintf('INV/%s/%04d/%s', $dateStr, $countToday, $random);
    }
}
