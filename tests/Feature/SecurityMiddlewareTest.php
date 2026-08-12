<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('registrasi publik fortify dinonaktifkan', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('kasir tidak dapat memproses transaksi pos jika shift kasir belum dibuka', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko Security', 'code' => 'SEC01']);
    $user = User::factory()->create(['store_id' => $store->id]);
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Kopi Hitam',
        'slug' => 'kopi-hitam',
        'buy_price' => 5000,
        'sell_price' => 10000,
        'stock' => 20,
    ]);

    // Transaksi tanpa shift aktif akan di-redirect ke halaman shift
    $response = $this->actingAs($user)->post(route('pos.transactions.store'), [
        'store_id' => $store->id,
        'payment_method' => 'cash',
        'paid_amount' => 10000,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'sell_price' => 10000],
        ],
    ]);

    $response->assertRedirect(route('shifts.index'));
});

test('staf tidak dapat mengakses manajemen user jika bukan owner atau manager', function () {
    $this->withoutVite();
    $this->seed(RoleAndPermissionSeeder::class);
    $store = Store::create(['name' => 'Toko Security 2', 'code' => 'SEC02']);
    $cashierUser = User::factory()->create(['store_id' => $store->id]);
    $cashierUser->assignRole('Kasir');

    $response = $this->actingAs($cashierUser)->get(route('users.index'));

    $response->assertForbidden();
});
