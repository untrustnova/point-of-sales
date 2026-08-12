<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Store;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('stokservice dapat mencatat mutasi stok masuk dan keluar', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST01']);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Kopi Robusta 1kg',
        'slug' => 'kopi-robusta-1kg',
        'buy_price' => 50000,
        'sell_price' => 75000,
        'stock' => 10,
    ]);

    $stockService = app(StockService::class);

    // Test Stok Masuk (+15)
    $stockService->recordMovement(
        storeId: $store->id,
        productId: $product->id,
        type: 'in',
        quantity: 15,
        notes: 'Pembelian barang'
    );

    expect($product->fresh()->stock)->toBe(25);

    // Test Stok Keluar / Sale (-10)
    $stockService->recordMovement(
        storeId: $store->id,
        productId: $product->id,
        type: 'sale',
        quantity: 10,
        notes: 'Penjualan kasir'
    );

    expect($product->fresh()->stock)->toBe(15);
});

test('stokservice melempar exception jika stok tidak mencukupi', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST02']);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Susu UHT 1L',
        'slug' => 'susu-uht-1l',
        'buy_price' => 12000,
        'sell_price' => 18000,
        'stock' => 5,
    ]);

    $stockService = app(StockService::class);

    // Mencoba memotong stok melebihi stok yang ada (memotong 10 dari 5)
    $stockService->recordMovement(
        storeId: $store->id,
        productId: $product->id,
        type: 'sale',
        quantity: 10
    );
})->throws(InsufficientStockException::class);
