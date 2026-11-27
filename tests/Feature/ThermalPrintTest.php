<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\ThermalReceiptService;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ThermalPrintTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(?string $role): User
    {
        $this->seed(PermissionsTableSeeder::class);
        $this->seed(RolesTableSeeder::class);

        $user = User::factory()->create();
        if ($role !== null) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function createCompletedOrder(User $actor): Order
    {
        $customer = Customer::factory()->create(['created_by' => $actor->id]);
        $category = Category::factory()->create(['created_by' => $actor->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $actor->id,
            'product_name' => 'Thermal Paper Roll',
            'product_code' => 'SKU-ROLL-80',
            'sale_price' => 25.00,
            'quantity' => 20,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'created_by' => $actor->id,
            'order_no' => 'ORD-THERM-001',
            'total_items' => 2,
            'sub_total' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'total' => 55.00,
            'payment_method' => 'cash',
            'paid_amount' => 60.00,
            'change_amount' => 5.00,
            'status' => Order::STATUS_COMPLETED,
            'notes' => 'Thermal print test order',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'sub_total' => 50.00,
        ]);

        return $order;
    }

    public function test_cashier_can_send_thermal_test_print_and_print_order_receipt(): void
    {
        $cashier = $this->createUserWithRole('cashier');
        $order = $this->createCompletedOrder($cashier);

        $this->partialMock(ThermalReceiptService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('printTest')
                ->once()
                ->withArgs(fn (array $options) => $options['driver'] === 'tcp'
                    && $options['host'] === '127.0.0.1'
                    && (int) $options['port'] === 9100
                    && (int) $options['paper_width'] === 80)
                ->andReturn([
                    'status' => true,
                    'message' => 'Test receipt sent to printer (127.0.0.1:9100, 140 bytes).',
                    'bytes' => 140,
                ]);

            $mock->shouldReceive('printOrder')
                ->once()
                ->withArgs(fn (Order $passedOrder, array $options) => $passedOrder->id === $order->id
                    && $options['driver'] === 'http'
                    && $options['host'] === '192.168.1.25'
                    && (int) $options['port'] === 9100
                    && (int) $options['paper_width'] === 58
                    && $options['open_cash_drawer'] === false)
                ->andReturn([
                    'status' => true,
                    'message' => "Receipt {$order->order_no} sent to printer (192.168.1.25:9100, 260 bytes).",
                    'bytes' => 260,
                ]);
        });

        $this->actingAs($cashier)
            ->postJson(route('orders.printer.test'), [
                'driver' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 9100,
                'paper_width' => 80,
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('bytes', 140);

        $this->actingAs($cashier)
            ->postJson(route('orders.printThermal', $order->id), [
                'driver' => 'http',
                'host' => '192.168.1.25',
                'port' => 9100,
                'http_path' => '/print',
                'http_format' => 'text',
                'paper_width' => 58,
                'open_cash_drawer' => false,
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('bytes', 260);
    }

    public function test_thermal_printer_endpoints_validate_ssrf_host_port_and_driver(): void
    {
        $cashier = $this->createUserWithRole('cashier');
        $order = $this->createCompletedOrder($cashier);

        $this->partialMock(ThermalReceiptService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('printTest');
            $mock->shouldNotReceive('printOrder');
        });

        $this->actingAs($cashier)
            ->postJson(route('orders.printer.test'), [
                'driver' => 'tcp',
                'host' => '169.254.169.254',
                'port' => 9100,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['host']);

        $this->actingAs($cashier)
            ->postJson(route('orders.printThermal', $order->id), [
                'driver' => 'browser',
                'host' => '8.8.8.8',
                'port' => 70000,
                'paper_width' => 42,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['driver', 'host', 'port', 'paper_width']);
    }

    public function test_unprivileged_user_cannot_trigger_thermal_printing(): void
    {
        $admin = $this->createUserWithRole('admin');
        $order = $this->createCompletedOrder($admin);
        $unprivileged = User::factory()->create();

        $this->actingAs($unprivileged)
            ->postJson(route('orders.printer.test'), ['driver' => 'tcp', 'host' => '127.0.0.1', 'port' => 9100])
            ->assertStatus(403);

        $this->actingAs($unprivileged)
            ->postJson(route('orders.printThermal', $order->id), ['driver' => 'tcp', 'host' => '127.0.0.1', 'port' => 9100])
            ->assertStatus(403);
    }

    public function test_receipt_view_supports_58mm_and_80mm_paper_widths_and_store_returns_print_url(): void
    {
        $cashier = $this->createUserWithRole('cashier');
        $customer = Customer::factory()->create(['created_by' => $cashier->id]);
        $category = Category::factory()->create(['created_by' => $cashier->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $cashier->id,
            'sale_price' => 40.00,
            'quantity' => 10,
        ]);

        $store = $this->actingAs($cashier)->postJson(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'discount' => 0,
            'tax_rate' => 0,
            'payment_method' => 'cash',
            'paid_amount' => 40.00,
        ]);

        $store->assertStatus(201);
        $orderId = $store->json('order.id');
        $store->assertJsonPath('print_thermal_url', route('orders.printThermal', $orderId));

        $this->actingAs($cashier)
            ->get(route('orders.receipt', ['order' => $orderId, 'width' => 80]))
            ->assertStatus(200)
            ->assertSee('Sales Receipt (80mm)')
            ->assertSee('Browser Print')
            ->assertSee('btn-success d-none" id="receipt-thermal-print-btn"', false);

        config()->set('pos.printer.driver', 'tcp');

        $this->actingAs($cashier)
            ->get(route('orders.receipt', ['order' => $orderId, 'width' => 58]))
            ->assertStatus(200)
            ->assertSee('Sales Receipt (58mm)')
            ->assertDontSee('btn-success d-none" id="receipt-thermal-print-btn"', false);
    }
}
