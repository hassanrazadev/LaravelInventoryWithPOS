<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReturnTest extends TestCase
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

    public function test_sales_return_crud_and_stock_restocking(): void
    {
        $manager = $this->createUserWithRole('manager');
        $category = Category::factory()->create(['created_by' => $manager->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $manager->id,
            'sale_price' => 50.00,
            'quantity' => 7,
        ]);
        $otherProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $manager->id,
            'sale_price' => 25.00,
            'quantity' => 10,
        ]);

        $order = Order::factory()->create([
            'created_by' => $manager->id,
            'total_items' => 3,
            'sub_total' => 150.00,
            'tax' => 0,
            'discount' => 0,
            'total' => 150.00,
            'paid_amount' => 150.00,
            'status' => Order::STATUS_COMPLETED,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 50.00,
            'sub_total' => 150.00,
        ]);

        $this->actingAs($manager)->get(route('returns.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('returns.create'))->assertStatus(200);

        // Order items JSON endpoint
        $this->actingAs($manager)->getJson(route('returns.orderItems', $order->id))
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.remaining_qty', 3);

        // Store return of 2 units with restock = true -> stock rises 7 -> 9
        $this->actingAs($manager)->post(route('returns.store'), [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'restock' => '1',
            'reason' => 'Wrong model ordered',
        ])->assertRedirect(route('returns.index'));

        $returnOne = SaleReturn::firstOrFail();
        $this->assertSame(9, (int) $product->fresh()->quantity);
        $this->assertEquals(100.00, (float) $returnOne->return_price);
        $this->assertTrue($returnOne->restock);

        // Remaining returnable should now be 1
        $this->actingAs($manager)->getJson(route('returns.orderItems', $order->id))
            ->assertStatus(200)
            ->assertJsonPath('items.0.returned_qty', 2)
            ->assertJsonPath('items.0.remaining_qty', 1);

        // Reject returning more than remaining (2 > 1)
        $this->actingAs($manager)->from(route('returns.create'))
            ->post(route('returns.store'), [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('returns.create'))
            ->assertSessionHasErrors('quantity');

        // Reject product not in order
        $this->actingAs($manager)->from(route('returns.create'))
            ->post(route('returns.store'), [
                'order_id' => $order->id,
                'product_id' => $otherProduct->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('returns.create'))
            ->assertSessionHasErrors('product_id');

        // Return final 1 unit with restock = false -> stock stays 9
        $this->actingAs($manager)->post(route('returns.store'), [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'return_price' => 45.00,
            'restock' => '0',
            'reason' => 'Damaged box',
        ])->assertRedirect(route('returns.index'));

        $this->assertSame(9, (int) $product->fresh()->quantity);

        // Show & Edit & Update metadata
        $this->actingAs($manager)->get(route('returns.show', $returnOne->id))
            ->assertStatus(200)
            ->assertSee($order->order_no)
            ->assertSee('Wrong model ordered');

        $this->actingAs($manager)->get(route('returns.edit', $returnOne->id))
            ->assertStatus(200)
            ->assertSee($order->order_no);

        $this->actingAs($manager)->put(route('returns.update', $returnOne->id), [
            'return_price' => 95.00,
            'reason' => 'Updated restocking fee applied',
        ])->assertRedirect(route('returns.index'));

        $returnOne->refresh();
        $this->assertEquals(95.00, (float) $returnOne->return_price);
        $this->assertSame('Updated restocking fee applied', $returnOne->reason);

        // Destroy restocked return reverses 2 units: 9 -> 7
        $this->actingAs($manager)->delete(route('returns.destroy', $returnOne->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('returns', ['id' => $returnOne->id]);
        $this->assertSame(7, (int) $product->fresh()->quantity);

        // Order show page renders remaining recorded return
        $this->actingAs($manager)->get(route('orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee('Recorded Returns')
            ->assertSee('Damaged box');
    }

    public function test_cancelled_orders_cannot_be_returned_and_rbac_is_enforced(): void
    {
        $manager = $this->createUserWithRole('manager');
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');

        $category = Category::factory()->create(['created_by' => $manager->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $manager->id,
            'quantity' => 5,
        ]);

        $cancelledOrder = Order::factory()->create([
            'created_by' => $manager->id,
            'status' => Order::STATUS_CANCELLED,
        ]);
        OrderDetail::create([
            'order_id' => $cancelledOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 20.00,
            'sub_total' => 20.00,
        ]);

        $this->actingAs($cashier)->get(route('returns.index'))->assertStatus(403);
        $this->actingAs($manager)->getJson(route('returns.orderItems', $cancelledOrder->id))->assertStatus(422);

        $this->actingAs($manager)->from(route('returns.create'))
            ->post(route('returns.store'), [
                'order_id' => $cancelledOrder->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('returns.create'))
            ->assertSessionHasErrors('order_id');
    }
}
