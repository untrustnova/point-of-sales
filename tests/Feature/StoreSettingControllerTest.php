<?php

use App\Models\Store;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('owner dapat mengakses dan memperbarui pengaturan toko', function () {
    $this->withoutVite();
    $this->seed(RoleAndPermissionSeeder::class);

    $store = Store::create(['name' => 'Toko Settings', 'code' => 'SET01']);
    $owner = User::factory()->create(['store_id' => $store->id]);
    $owner->assignRole('Owner');

    $responseView = $this->actingAs($owner)->get(route('store-settings.show'));
    $responseView->assertOk();

    $responseUpdate = $this->actingAs($owner)->put(route('store-settings.update'), [
        'name' => 'Toko POS Nova Kebayoran',
        'phone' => '081122334455',
        'address' => 'Jl. Kebayoran Baru No. 10',
        'settings' => [
            'tax_percentage' => 11,
            'receipt_footer' => 'Terima Kasih Atas Kunjungan Anda!',
        ],
    ]);

    $responseUpdate->assertRedirect();
    expect($store->fresh()->name)->toBe('Toko POS Nova Kebayoran');
});
