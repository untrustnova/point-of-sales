<?php

use App\Models\Store;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('owner atau manager dapat melihat log aktivitas staf', function () {
    $this->withoutVite();
    $this->seed(RoleAndPermissionSeeder::class);

    $store = Store::create(['name' => 'Toko Log Test', 'code' => 'LOGF01']);
    $owner = User::factory()->create(['store_id' => $store->id]);
    $owner->assignRole('Owner');

    $response = $this->actingAs($owner)->get(route('activity-logs.index'));

    $response->assertOk();
});
