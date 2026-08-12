<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('po service dapat membuat purchase order dan memproses penerimaan stok', function () {
    $store = Store::create(['name' => 'Toko PO Test', 'code' => 'PO01']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $supplier = Supplier::create(['store_id' => $store->id, 'name' => 'Supplier Utama']);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Gula Pasir 1kg',
        'slug' => 'gula-pasir-1kg',
        'buy_price' => 12000,
        'sell_price' => 15000,
        'stock' => 10,
    ]);

    $poService = app(PurchaseOrderService::class);

    $po = $poService->createPo([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'order_date' => now()->toDateString(),
        'items' => [
            [
                'product_id' => $product->id,
                'quantity_ordered' => 20,
                'unit_cost' => 12000,
            ],
        ],
    ]);

    expect($po->status)->toBe('ordered')
        ->and((float) $po->total_amount)->toBe(240000.0);

    $poItem = $po->items->first();

    // Memproses penerimaan 20 unit barang
    $poService->receiveItems($po, [
        [
            'item_id' => $poItem->id,
            'quantity_received_now' => 20,
        ],
    ], $user->id);

    expect($po->fresh()->status)->toBe('received')
        ->and($product->fresh()->stock)->toBe(30); // 10 + 20
});
