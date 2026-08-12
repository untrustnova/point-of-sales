<?php

use App\Events\LowStockAlertEvent;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Event;

test('artisan pos:check-low-stock mengirimkan event low stock alert', function () {
    Event::fake([LowStockAlertEvent::class]);

    $store = Store::create(['name' => 'Toko Alert Test', 'code' => 'ALT01']);
    Product::create([
        'store_id' => $store->id,
        'name' => 'Produk Kritis',
        'slug' => 'produk-kritis',
        'buy_price' => 5000,
        'sell_price' => 8000,
        'stock' => 2,
        'min_stock_alert' => 5,
    ]);

    $this->artisan('pos:check-low-stock')
        ->assertSuccessful();

    Event::assertDispatched(LowStockAlertEvent::class);
});
