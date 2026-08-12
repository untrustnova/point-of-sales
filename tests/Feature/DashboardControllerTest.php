<?php

use App\Models\Store;
use App\Models\User;

test('authenticated user dapat mengakses dashboard dengan real pos metrics', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko Dashboard Test', 'code' => 'DSH01']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $response = $this->actingAs($user)->get(route('dashboard.main'));

    $response->assertOk();
});
