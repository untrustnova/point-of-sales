<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    public function __construct(
        public StockService $stockService
    ) {}

    public function createPo(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $storeId = $data['store_id'];
            $poNumber = 'PO/'.Carbon::now()->format('Ymd').'/'.strtoupper(Str::random(4));

            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $product = Product::where('store_id', $storeId)->findOrFail($item['product_id']);
                $qty = (int) $item['quantity_ordered'];
                $cost = (float) $item['unit_cost'];
                $itemSubtotal = $qty * $cost;

                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_cost' => $cost,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'store_id' => $storeId,
                'supplier_id' => $data['supplier_id'],
                'user_id' => $data['user_id'],
                'po_number' => $poNumber,
                'order_date' => $data['order_date'] ?? Carbon::today(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'ordered',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $po->items()->create($itemData);
            }

            return $po;
        });
    }

    /**
     * Memproses penerimaan barang dari supplier & otomatis memperbarui stok.
     */
    public function receiveItems(PurchaseOrder $po, array $receivedData, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $receivedData, $userId) {
            $allReceived = true;

            foreach ($receivedData as $receivedItem) {
                $item = $po->items()->where('id', $receivedItem['item_id'])->first();

                if ($item) {
                    $qtyReceivedNow = (int) $receivedItem['quantity_received_now'];

                    if ($qtyReceivedNow > 0) {
                        $item->increment('quantity_received', $qtyReceivedNow);

                        // Tambahkan stok via StockService
                        $this->stockService->recordMovement(
                            storeId: $po->store_id,
                            productId: $item->product_id,
                            type: 'purchase',
                            quantity: $qtyReceivedNow,
                            variantId: $item->product_variant_id,
                            userId: $userId,
                            referenceType: PurchaseOrder::class,
                            referenceId: $po->id,
                            notes: "Penerimaan barang PO: {$po->po_number}"
                        );
                    }

                    if ($item->quantity_received < $item->quantity_ordered) {
                        $allReceived = false;
                    }
                }
            }

            $po->update([
                'status' => $allReceived ? 'received' : 'partial',
            ]);

            return $po;
        });
    }
}
