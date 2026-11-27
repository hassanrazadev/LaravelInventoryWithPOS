<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent_and_creates_expected_records(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, Role::where('name', 'admin')->count());
        $this->assertSame(1, Role::where('name', 'manager')->count());
        $this->assertSame(1, Role::where('name', 'cashier')->count());
        $this->assertSame(44, Permission::count());
        $this->assertDatabaseHas('permissions', ['name' => 'create purchase']);
        $this->assertDatabaseHas('permissions', ['name' => 'create user']);
        $this->assertDatabaseHas('permissions', ['name' => 'create role']);
        $this->assertDatabaseHas('permissions', ['name' => 'create permission']);

        $admin = User::where('email', 'admin@test.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('admin'));

        $manager = User::where('email', 'manager@test.com')->first();
        $this->assertNotNull($manager);
        $this->assertTrue($manager->hasRole('manager'));

        $cashier = User::where('email', 'cashier@test.com')->first();
        $this->assertNotNull($cashier);
        $this->assertTrue($cashier->hasRole('cashier'));

        $this->assertSame(6, Category::withTrashed()->count());
        $this->assertSame(1, Category::onlyTrashed()->count());
        $this->assertSame(8, Product::count());
        $this->assertSame(3, Supplier::count());
        $this->assertSame(3, Customer::count());
        $this->assertSame(3, Purchase::count());
        $this->assertSame(2, Order::count());
        $this->assertSame(1, SaleReturn::count());
        $this->assertSame(3, Expense::count());

        $this->assertSame(
            5,
            (int) Product::where('product_slug', 'dell-latitude-5420-laptop')->value('quantity')
        );
        $this->assertSame(
            18,
            (int) Product::where('product_slug', 'logitech-mx-master-3s-mouse')->value('quantity')
        );
        $this->assertSame(
            48,
            (int) Product::where('product_slug', 'sandisk-ultra-128gb-usb-flash')->value('quantity')
        );

        $seededCategory = Category::where('category_slug', 'electronics')->firstOrFail();
        $seededProduct = Product::where('product_slug', 'dell-latitude-5420-laptop')->firstOrFail();
        Storage::disk('public')->assertExists($seededCategory->category_image);
        Storage::disk('public')->assertExists($seededProduct->product_image);
    }
}
