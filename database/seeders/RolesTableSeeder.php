<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $managerPermissions = [];
        foreach (['category', 'product', 'purchase', 'order', 'supplier', 'customer', 'expense', 'return'] as $module) {
            foreach (['create', 'read', 'update', 'delete'] as $action) {
                $managerPermissions[] = "{$action} {$module}";
            }
        }
        $manager->syncPermissions($managerPermissions);

        $cashier = Role::firstOrCreate([
            'name' => 'cashier',
            'guard_name' => 'web',
        ]);
        $cashier->syncPermissions([
            'read category',
            'read product',
            'create order',
            'read order',
            'create customer',
            'read customer',
        ]);
    }
}
