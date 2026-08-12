<?php

use App\Models\Store;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

test('authenticated user dapat mengunduh daftar produk ke excel', function () {
    Excel::fake();
    $store = Store::create(['name' => 'Toko Excel Test', 'code' => 'EXC01']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $response = $this->actingAs($user)->get(route('products.export'));

    $response->assertOk();
    Excel::assertDownloaded('Daftar_Produk_POS_Nova.xlsx');
});
