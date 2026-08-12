<?php

use App\Models\Store;
use App\Models\User;

test('authenticated user dapat melihat dan mengunduh laporan penjualan pdf', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko POS Report', 'code' => 'TK06']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $responseView = $this->actingAs($user)->get(route('reports.sales'));
    $responseView->assertOk();

    $responsePdf = $this->actingAs($user)->get(route('reports.sales.pdf'));
    $responsePdf->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
