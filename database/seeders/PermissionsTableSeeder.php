<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'create category',
            'read category',
            'update category',
            'delete category',
            'create product',
            'read product',
            'update product',
            'delete product',
            'create purchase',
            'read purchase',
            'update purchase',
            'delete purchase',
            'create order',
            'read order',
            'update order',
            'delete order',
            'create expense',
            'read expense',
            'update expense',
            'delete expense',
            'create supplier',
            'read supplier',
            'update supplier',
            'delete supplier',
            'create customer',
            'read customer',
            'update customer',
            'delete customer',
            'create return',
            'read return',
            'update return',
            'delete return',
            'create user',
            'read user',
            'update user',
            'delete user',
            'create role',
            'read role',
            'update role',
            'delete role',
            'create permission',
            'read permission',
            'update permission',
            'delete permission',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
