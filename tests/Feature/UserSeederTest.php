<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user seeder populates predefined role users and random dummy users', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(14);

    // Verify predefined users
    $owner = User::where('email', 'owner@posnova.test')->first();
    expect($owner)->not->toBeNull()
        ->and($owner->hasRole('Owner'))->toBeTrue()
        ->and($owner->store_id)->not->toBeNull();

    $manager = User::where('email', 'manager@posnova.test')->first();
    expect($manager)->not->toBeNull()
        ->and($manager->hasRole('Manager'))->toBeTrue()
        ->and($manager->store_id)->not->toBeNull();

    $cashier = User::where('email', 'kasir@posnova.test')->first();
    expect($cashier)->not->toBeNull()
        ->and($cashier->hasRole('Kasir'))->toBeTrue()
        ->and($cashier->store_id)->not->toBeNull();

    $warehouse = User::where('email', 'gudang@posnova.test')->first();
    expect($warehouse)->not->toBeNull()
        ->and($warehouse->hasRole('Staff Gudang'))->toBeTrue()
        ->and($warehouse->store_id)->not->toBeNull();

    // Verify all users have store_id and at least one role
    User::all()->each(function (User $user) {
        expect($user->store_id)->not->toBeNull()
            ->and($user->roles)->not->toBeEmpty();
    });
});
