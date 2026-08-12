<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ShiftService;

test('authenticated user dapat mengakses halaman kasir pos', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko POS Kasir', 'code' => 'TK03']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $response = $this->actingAs($user)->get(route('pos.index'));

    $response->assertOk();
});

test('kasir dapat memproses transaksi penjualan dari pos endpoint', function () {
    $store = Store::create(['name' => 'Toko POS Kasir 2', 'code' => 'TK04']);
    $user = User::factory()->create(['store_id' => $store->id]);
    app(ShiftService::class)->openShift($store->id, $user->id, 100000);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Detergen 1kg',
        'slug' => 'detergen-1kg',
        'buy_price' => 15000,
        'sell_price' => 22000,
        'stock' => 30,
    ]);

    $response = $this->actingAs($user)->postJson(route('pos.transactions.store'), [
        'store_id' => $store->id,
        'payment_method' => 'cash',
        'paid_amount' => 50000,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'sell_price' => 22000,
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect($product->fresh()->stock)->toBe(28);
});
