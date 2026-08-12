<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first() ?? Store::factory()->create();

        // 1. Predefined Accounts for all roles
        $defaultUsers = [
            [
                'name' => 'Owner Toko',
                'email' => 'owner@posnova.test',
                'role' => 'Owner',
            ],
            [
                'name' => 'Manager Toko',
                'email' => 'manager@posnova.test',
                'role' => 'Manager',
            ],
            [
                'name' => 'Kasir Utama',
                'email' => 'kasir@posnova.test',
                'role' => 'Kasir',
            ],
            [
                'name' => 'Staff Gudang',
                'email' => 'gudang@posnova.test',
                'role' => 'Staff Gudang',
            ],
        ];

        foreach ($defaultUsers as $userData) {
            $user = User::firstWhere('email', $userData['email']);

            if (! $user) {
                $user = User::factory()->create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'store_id' => $store->id,
                ]);
            } else {
                $user->update(['store_id' => $store->id]);
            }

            $user->syncRoles([$userData['role']]);
        }

        // 2. Create 10 dummy users with randomized attributes and assigned roles
        $roles = ['Kasir', 'Staff Gudang', 'Manager'];

        User::factory()->count(10)->create([
            'store_id' => $store->id,
        ])->each(function (User $user) use ($roles) {
            $randomRole = $roles[array_rand($roles)];
            $user->assignRole($randomRole);
        });
    }
}
