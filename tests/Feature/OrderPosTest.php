<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPosTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $role): User
    {
        $this->seed(PermissionsTableSeeder::class);
        $this->seed(RolesTableSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_pos_terminal_catalog_search_and_atomic_checkout(): void
    {
        $cashier = $this->createUserWithRole('cashier');
        $customer = Customer::factory()->create(['created_by' => $cashier->id]);
        $catA = Category::factory()->create(['category_name' => 'Hardware', 'created_by' => $cashier->id]);
        $catB = Category::factory()->create(['category_name' => 'Stationery', 'created_by' => $cashier->id]);

        $laptop = Product::factory()->create([
            'category_id' => $catA->id,
            'created_by' => $cashier->id,
            'product_name' => 'Pro Laptop',
            'product_code' => 'SKU-LAP-1',
            'sale_price' => 100.00,
            'quantity' => 5,
        ]);

        $pen = Product::factory()->create([
            'category_id' => $catB->id,
            'created_by' => $cashier->id,
            'product_name' => 'Gel Pen Pack',
            'product_code' => 'SKU-PEN-2',
            'sale_price' => 10.00,
            'quantity' => 12,
        ]);

        $this->actingAs($cashier)->get(route('orders.create'))
            ->assertStatus(200)
            ->assertSee('Pro Laptop')
            ->assertSee($customer->name);

        // Live product search filter endpoint
        $search = $this->actingAs($cashier)->getJson(route('orders.products', ['q' => 'SKU-LAP', 'category_id' => $catA->id]));
        $search->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.code', 'SKU-LAP-1');

        // Checkout: (2 * 100) + (3 * 10) = 230.00, discount 30.00 -> taxable 200.00, tax 10% = 20.00 -> total 220.00
        $checkout = $this->actingAs($cashier)->postJson(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $laptop->id, 'quantity' => 2],
                ['product_id' => $pen->id, 'quantity' => 3],
            ],
            'discount' => 30.00,
            'tax_rate' => 10,
            'payment_method' => 'cash',
            'paid_amount' => 250.00,
            'notes' => 'Front counter POS sale',
        ]);

        $checkout->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('order.total', '220.00')
            ->assertJsonPath('order.change_amount', '30.00')
            ->assertJsonPath('order.total_items', 5);

        $order = Order::firstOrFail();
        $this->assertSame($customer->id, (int) $order->customer_id);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertEquals(230.00, (float) $order->sub_total);
        $this->assertEquals(20.00, (float) $order->tax);
        $this->assertEquals(30.00, (float) $order->discount);
        $this->assertEquals(220.00, (float) $order->total);
        $this->assertEquals(30.00, (float) $order->change_amount);

        // Stock decremented atomically
        $this->assertSame(3, (int) $laptop->fresh()->quantity);
        $this->assertSame(9, (int) $pen->fresh()->quantity);

        $this->assertSame(2, OrderDetail::where('order_id', $order->id)->count());

        // Order index, detail, and receipt pages
        $this->actingAs($cashier)->get(route('orders.index'))
            ->assertStatus(200)
            ->assertSee($order->order_no);

        $this->actingAs($cashier)->get(route('orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee($order->order_no)
            ->assertSee('Pro Laptop');

        $this->actingAs($cashier)->get(route('orders.receipt', $order->id))
            ->assertStatus(200)
            ->assertSee($order->order_no)
            ->assertSee('Sales Receipt')
            ->assertSee('220.00');
    }

    public function test_pos_checkout_validates_stock_discount_and_payment(): void
    {
        $admin = $this->createUserWithRole('admin');
        $category = Category::factory()->create(['created_by' => $admin->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'sale_price' => 50.00,
            'quantity' => 2,
        ]);

        // Insufficient stock fails atomically
        $this->actingAs($admin)->postJson(route('orders.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
            'discount' => 0,
            'tax_rate' => 0,
            'payment_method' => 'cash',
            'paid_amount' => 200.00,
        ])->assertStatus(422)->assertJsonValidationErrors('items');

        $this->assertSame(0, Order::count());
        $this->assertSame(2, (int) $product->fresh()->quantity);

        // Underpayment fails atomically
        $this->actingAs($admin)->postJson(route('orders.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'discount' => 0,
            'tax_rate' => 0,
            'payment_method' => 'cash',
            'paid_amount' => 90.00,
        ])->assertStatus(422)->assertJsonValidationErrors('paid_amount');

        $this->assertSame(0, Order::count());
        $this->assertSame(2, (int) $product->fresh()->quantity);
    }

    public function test_order_cancel_and_destroy_restore_stock_without_double_restoring(): void
    {
        $manager = $this->createUserWithRole('manager');
        $category = Category::factory()->create(['created_by' => $manager->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $manager->id,
            'sale_price' => 40.00,
            'quantity' => 10,
        ]);

        // Order #1: cancel then destroy -> stock returns to 10 (never 12)
        $this->actingAs($manager)->postJson(route('orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_method' => 'card',
            'paid_amount' => 120.00,
        ])->assertStatus(201);

        $orderOne = Order::firstOrFail();
        $this->assertSame(7, (int) $product->fresh()->quantity);

        $this->actingAs($manager)->patchJson(route('orders.cancel', $orderOne->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertSame(Order::STATUS_CANCELLED, $orderOne->fresh()->status);
        $this->assertSame(10, (int) $product->fresh()->quantity);

        // Second cancel does not double-restore
        $this->actingAs($manager)->patchJson(route('orders.cancel', $orderOne->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);
        $this->assertSame(10, (int) $product->fresh()->quantity);

        // Destroying an already-cancelled order does not double-restore
        $this->actingAs($manager)->deleteJson(route('orders.destroy', $orderOne->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertSame(10, (int) $product->fresh()->quantity);

        // Order #2: deleting an active completed order DOES restore stock
        $this->actingAs($manager)->postJson(route('orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
            'payment_method' => 'cash',
            'paid_amount' => 160.00,
        ])->assertStatus(201);

        $orderTwo = Order::firstOrFail();
        $this->assertSame(6, (int) $product->fresh()->quantity);

        $this->actingAs($manager)->deleteJson(route('orders.destroy', $orderTwo->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertSame(10, (int) $product->fresh()->quantity);
    }
}
