<?php

namespace App\Services;

use App\Events\LowStockAlertEvent;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class StockService
{
    /**
     * Catat mutasi stok dan perbarui kuantitas produk/varian.
     *
     * @throws InsufficientStockException
     */
    public function recordMovement(
        int $storeId,
        int $productId,
        string $type,
        int $quantity,
        ?int $variantId = null,
        ?int $userId = null,
        ?int $warehouseId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use (
            $storeId,
            $productId,
            $type,
            $quantity,
            $variantId,
            $userId,
            $warehouseId,
            $referenceType,
            $referenceId,
            $notes
        ) {
            $product = Product::where('store_id', $storeId)
                ->lockForUpdate()
                ->findOrFail($productId);

            $target = $product;
            if ($variantId !== null) {
                $target = ProductVariant::where('product_id', $productId)
                    ->lockForUpdate()
                    ->findOrFail($variantId);
            }

            $balanceBefore = $target->stock;
            $delta = $this->calculateStockDelta($type, $quantity);
            $balanceAfter = $balanceBefore + $delta;

            if ($balanceAfter < 0) {
                throw new InsufficientStockException(
                    "Stok produk '{$product->name}'".($variantId ? " ({$target->name})" : '')." tidak cukup. Stok saat ini: {$balanceBefore}, dibutuhkan: ".abs($delta)
                );
            }

            $target->stock = $balanceAfter;
            $target->save();

            // Jika varian di-update, update juga total agregat stok di produk utama
            if ($variantId !== null) {
                $product->stock = ProductVariant::where('product_id', $productId)->sum('stock');
                $product->save();
            }

            if ($product->stock <= $product->min_stock_alert) {
                Event::dispatch(new LowStockAlertEvent($product));
            }

            return StockMovement::create([
                'store_id' => $storeId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    private function calculateStockDelta(string $type, int $quantity): int
    {
        $absQty = abs($quantity);

        return match ($type) {
            'in', 'purchase', 'return' => $absQty,
            'out', 'sale' => -$absQty,
            'adjustment' => $quantity, // bisa positif atau negatif
            default => 0,
        };
    }
}
