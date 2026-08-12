<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('authenticated user dapat melihat daftar purchase orders', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko PO Feature', 'code' => 'POF01']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $response = $this->actingAs($user)->get(route('purchase-orders.index'));
    $response->assertOk();
});

test('user dapat membuat po via controller', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko PO Feature 2', 'code' => 'POF02']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $supplier = Supplier::create(['store_id' => $store->id, 'name' => 'Supplier Berkah']);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Minyak Goreng 2L',
        'slug' => 'minyak-goreng-2l',
        'buy_price' => 25000,
        'sell_price' => 32000,
        'stock' => 5,
    ]);

    $response = $this->actingAs($user)->post(route('purchase-orders.store'), [
        'supplier_id' => $supplier->id,
        'order_date' => now()->toDateString(),
        'items' => [
            [
                'product_id' => $product->id,
                'quantity_ordered' => 10,
                'unit_cost' => 25000,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('purchase_orders', [
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
    ]);
});
