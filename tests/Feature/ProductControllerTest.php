<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;

test('authenticated user dapat melihat daftar produk', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko POS', 'code' => 'TK01']);
    $user = User::factory()->create(['store_id' => $store->id]);

    Product::create([
        'store_id' => $store->id,
        'name' => 'Kopi Latte 250ml',
        'slug' => 'kopi-latte-250ml',
        'buy_price' => 10000,
        'sell_price' => 18000,
        'stock' => 20,
    ]);

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertOk();
});

test('user dapat menambah produk baru via controller', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko POS 2', 'code' => 'TK02']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $category = Category::create(['store_id' => $store->id, 'name' => 'Minuman', 'slug' => 'minuman']);
    $unit = Unit::create(['store_id' => $store->id, 'name' => 'Botol', 'short_name' => 'btl']);

    $response = $this->actingAs($user)->post(route('products.store'), [
        'store_id' => $store->id,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'name' => 'Teh Botol 450ml',
        'buy_price' => 3000,
        'sell_price' => 5000,
        'stock' => 50,
        'min_stock_alert' => 10,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', [
        'store_id' => $store->id,
        'name' => 'Teh Botol 450ml',
    ]);
});
