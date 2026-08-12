<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transaction service memproses transaksi kasir dan memotong stok', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST05']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Air Mineral 600ml',
        'slug' => 'air-mineral-600ml',
        'buy_price' => 2000,
        'sell_price' => 4000,
        'stock' => 100,
    ]);

    $transactionService = app(TransactionService::class);

    $transaction = $transactionService->createTransaction([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'payment_method' => 'cash',
        'paid_amount' => 10000,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'sell_price' => 4000,
            ],
        ],
    ]);

    expect($transaction->status)->toBe('completed')
        ->and((float) $transaction->total_amount)->toBe(8000.0)
        ->and((float) $transaction->change_amount)->toBe(2000.0)
        ->and($product->fresh()->stock)->toBe(98);
});

test('transaction service dapat meng-void transaksi dan mengembalikan stok', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST06']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Snack Kentang 50g',
        'slug' => 'snack-kentang-50g',
        'buy_price' => 5000,
        'sell_price' => 10000,
        'stock' => 50,
    ]);

    $transactionService = app(TransactionService::class);

    $transaction = $transactionService->createTransaction([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'payment_method' => 'cash',
        'paid_amount' => 10000,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'sell_price' => 10000,
            ],
        ],
    ]);

    expect($product->fresh()->stock)->toBe(49);

    // Void transaksi
    $voidedTransaction = $transactionService->voidTransaction($transaction->id, $user->id, 'Pelanggan membatalkan pesanan');

    expect($voidedTransaction->status)->toBe('cancelled')
        ->and($product->fresh()->stock)->toBe(50);
});
