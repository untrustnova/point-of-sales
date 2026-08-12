<?php

use App\Exceptions\ShiftAlreadyClosedException;
use App\Models\Store;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('shift service dapat membuka dan menutup shift kasir', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST03']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $shiftService = app(ShiftService::class);

    // Buka shift dengan modal awal 100.000
    $shift = $shiftService->openShift($store->id, $user->id, 100000);

    expect($shift->status)->toBe('open')
        ->and((float) $shift->starting_cash)->toBe(100000.0);

    // Tutup shift dengan uang kas fisik 100.000 (selisih 0)
    $closedShift = $shiftService->closeShift($shift->id, 100000);

    expect($closedShift->status)->toBe('closed')
        ->and((float) $closedShift->cash_difference)->toBe(0.0);
});

test('shift service menolak membuka 2 shift bersamaan untuk user yang sama', function () {
    $store = Store::create(['name' => 'Store Test', 'code' => 'ST04']);
    $user = User::factory()->create(['store_id' => $store->id]);

    $shiftService = app(ShiftService::class);

    $shiftService->openShift($store->id, $user->id, 50000);
    $shiftService->openShift($store->id, $user->id, 50000);
})->throws(ShiftAlreadyClosedException::class);
