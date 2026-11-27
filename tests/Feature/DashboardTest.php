<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $this->seed(PermissionsTableSeeder::class);
        $this->seed(RolesTableSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_dashboard_renders_cleanly_on_empty_database(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Store Overview')
            ->assertSee('$0.00')
            ->assertSee('No completed orders yet.');
    }

    public function test_dashboard_computes_real_kpis_charts_and_low_stock_alerts(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $admin->id]);
        $supplier = Supplier::factory()->create(['created_by' => $admin->id]);
        $customer = Customer::factory()->create(['created_by' => $admin->id, 'name' => 'Zara Tariq']);

        $lowProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'product_name' => 'Low Stock Printer',
            'product_code' => 'SKU-LOW-1',
            'sale_price' => 150.00,
            'quantity' => 3,
        ]);
        $healthyProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'product_name' => 'Wireless Mouse',
            'product_code' => 'SKU-HI-2',
            'sale_price' => 40.00,
            'quantity' => 25,
        ]);

        Purchase::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'total' => 500.00,
        ]);

        Expense::factory()->create([
            'expense_name' => 'Utility Bill',
            'expense_amount' => 125.00,
            'created_by' => $admin->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'order_no' => 'ORD-DASH-001',
            'total_items' => 4,
            'sub_total' => 310.00,
            'tax' => 0,
            'discount' => 0,
            'total' => 310.00,
            'payment_method' => 'cash',
            'paid_amount' => 310.00,
            'status' => Order::STATUS_COMPLETED,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $lowProduct->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'sub_total' => 150.00,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $healthyProduct->id,
            'quantity' => 4,
            'unit_price' => 40.00,
            'sub_total' => 160.00,
        ]);

        SaleReturn::factory()->create([
            'order_id' => $order->id,
            'product_id' => $healthyProduct->id,
            'quantity' => 1,
            'return_price' => 40.00,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200)
            ->assertViewHas('kpis', function (array $kpis): bool {
                return $kpis['total_revenue'] === 310.0
                    && $kpis['total_purchases'] === 500.0
                    && $kpis['total_expenses'] === 125.0
                    && $kpis['total_returns'] === 40.0
                    && $kpis['low_stock_count'] === 1
                    && $kpis['total_stock_units'] === 28;
            })
            ->assertViewHas('chartSeries', fn (array $series): bool => count($series['labels']) === 7 && $series['seven_day_sales'] === 310.0 && $series['seven_day_purchases'] === 500.0)
            ->assertSee('$310.00')
            ->assertSee('$625.00')
            ->assertSee('$40.00')
            ->assertSee('ORD-DASH-001')
            ->assertSee('Zara Tariq')
            ->assertSee('Low Stock Printer')
            ->assertSee('3 left')
            ->assertSee('Wireless Mouse');
    }
}
