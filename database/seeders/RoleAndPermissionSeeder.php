<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'view-dashboard',
            'manage-users',
            'manage-products',
            'manage-categories',
            'manage-units',
            'manage-stock',
            'manage-suppliers',
            'manage-customers',
            'access-pos',
            'void-transaction',
            'manage-expenses',
            'view-reports',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view-dashboard',
            'manage-products',
            'manage-categories',
            'manage-units',
            'manage-stock',
            'manage-suppliers',
            'manage-customers',
            'access-pos',
            'void-transaction',
            'manage-expenses',
            'view-reports',
        ]);

        $cashier = Role::firstOrCreate(['name' => 'Kasir']);
        $cashier->givePermissionTo([
            'access-pos',
            'manage-customers',
        ]);

        $warehouse = Role::firstOrCreate(['name' => 'Staff Gudang']);
        $warehouse->givePermissionTo([
            'manage-products',
            'manage-categories',
            'manage-units',
            'manage-stock',
            'manage-suppliers',
        ]);
    }
}
