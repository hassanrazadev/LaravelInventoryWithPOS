<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('permissions')->truncate();
        $permissions = [
            [
                'name' => 'create category',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read category',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update category',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete category',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create product',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read product',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update product',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete product',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create order',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read order',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update order',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete order',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create expense',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read expense',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update expense',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete expense',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create supplier',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read supplier',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update supplier',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete supplier',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create customer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read customer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update customer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete customer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create return',
                'guard_name' => 'web',
            ],
            [
                'name' => 'read return',
                'guard_name' => 'web',
            ],
            [
                'name' => 'update return',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete return',
                'guard_name' => 'web',
            ],
        ];

        foreach ($permissions as $permission){
            Permission::create($permission);
        }
    }
}
