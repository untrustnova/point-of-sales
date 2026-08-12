<?php

use App\Models\Store;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('owner dapat melihat dan menambahkan staf baru', function () {
    $this->withoutVite();
    $this->seed(RoleAndPermissionSeeder::class);
    $store = Store::create(['name' => 'Toko Owner', 'code' => 'OWN01']);
    $owner = User::factory()->create(['store_id' => $store->id]);
    $owner->assignRole('Owner');

    $responseView = $this->actingAs($owner)->get(route('users.index'));
    $responseView->assertOk();

    $responseCreate = $this->actingAs($owner)->post(route('users.store'), [
        'name' => 'Kasir Baru Sinta',
        'email' => 'sinta@posnova.test',
        'password' => 'password123',
        'role' => 'Kasir',
    ]);

    $responseCreate->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'sinta@posnova.test',
        'store_id' => $store->id,
    ]);
});
