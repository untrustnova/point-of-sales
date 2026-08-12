<?php

use App\Models\CashierShift;
use App\Models\Store;
use App\Models\User;

test('kasir dapat membuka dan menutup shift via shift controller', function () {
    $this->withoutVite();
    $store = Store::create(['name' => 'Toko POS Shift', 'code' => 'TK05']);
    $user = User::factory()->create(['store_id' => $store->id]);

    // Buka shift
    $responseOpen = $this->actingAs($user)->post(route('shifts.open'), [
        'store_id' => $store->id,
        'starting_cash' => 150000,
        'notes' => 'Awal shift pagi',
    ]);

    $responseOpen->assertRedirect();
    $this->assertDatabaseHas('cashier_shifts', [
        'store_id' => $store->id,
        'user_id' => $user->id,
        'status' => 'open',
    ]);

    $shift = CashierShift::where('user_id', $user->id)->first();

    // Tutup shift
    $responseClose = $this->actingAs($user)->post(route('shifts.close', $shift), [
        'actual_ending_cash' => 150000,
        'notes' => 'Shift selesai rapi',
    ]);

    $responseClose->assertRedirect();
    expect($shift->fresh()->status)->toBe('closed');
});
