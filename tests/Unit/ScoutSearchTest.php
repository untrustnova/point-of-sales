<?php

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('model product dapat dicari menggunakan laravel scout database engine', function () {
    $store = Store::create(['name' => 'Store Scout Test', 'code' => 'SCT01']);

    Product::create([
        'store_id' => $store->id,
        'name' => 'Espresso Coffee Beans 1kg',
        'slug' => 'espresso-coffee-beans-1kg',
        'sku' => 'ESP-100',
        'barcode' => '8999999901',
        'buy_price' => 100000,
        'sell_price' => 150000,
        'stock' => 20,
    ]);

    Product::create([
        'store_id' => $store->id,
        'name' => 'Matcha Green Tea Powder 500g',
        'slug' => 'matcha-green-tea-powder-500g',
        'sku' => 'MTC-500',
        'barcode' => '8999999902',
        'buy_price' => 80000,
        'sell_price' => 120000,
        'stock' => 15,
    ]);

    $searchResults = Product::search('Espresso')->get();

    expect($searchResults->count())->toBeGreaterThanOrEqual(1)
        ->and($searchResults->first()->name)->toContain('Espresso');
});
