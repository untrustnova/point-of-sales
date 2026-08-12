<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\TransactionService;

test('authenticated user dapat melihat dan mengunduh pdf struk belanja thermal', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko Receipt Test', 'code' => 'RCP01']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Kopi Kapal Api',
        'slug' => 'kopi-kapal-api',
        'buy_price' => 2000,
        'sell_price' => 3000,
        'stock' => 50,
    ]);

    $transaction = app(TransactionService::class)->createTransaction([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'paid_amount' => 5000,
        'payment_method' => 'cash',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'sell_price' => 3000],
        ],
    ]);

    $responseView = $this->actingAs($user)->get(route('receipt.show', $transaction));
    $responseView->assertOk();

    $responsePdf = $this->actingAs($user)->get(route('receipt.pdf', $transaction));
    $responsePdf->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
