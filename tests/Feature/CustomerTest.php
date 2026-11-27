<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    public function test_customer_crud_and_soft_delete_flow(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('customers.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('customers.create'))->assertStatus(200);

        $store = $this->actingAs($admin)->post(route('customers.store'), [
            'name' => 'Bilal Ahmed',
            'email' => 'bilal@example.test',
            'phone' => '+92-300-9998877',
            'address' => 'Lahore, Pakistan',
        ]);

        $store->assertRedirect(route('customers.index'));
        $customer = Customer::where('email', 'bilal@example.test')->firstOrFail();

        $this->actingAs($admin)->get(route('customers.show', $customer->id))
            ->assertStatus(200)
            ->assertSee('Bilal Ahmed');

        $this->actingAs($admin)->get(route('customers.edit', $customer->id))
            ->assertStatus(200)
            ->assertSee('Bilal Ahmed');

        $this->actingAs($admin)->put(route('customers.update', $customer->id), [
            'name' => 'Bilal Raza',
            'email' => 'bilal@example.test',
            'phone' => '+92-300-1112222',
            'address' => 'Islamabad, Pakistan',
        ])->assertRedirect(route('customers.index'));

        $customer->refresh();
        $this->assertSame('Bilal Raza', $customer->name);

        $this->actingAs($admin)->patch(route('customers.delete', $customer->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);

        $this->actingAs($admin)->patch(route('customers.delete', $customer->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);

        $this->actingAs($admin)->delete(route('customers.destroy', $customer->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_pos_quick_add_customer_returns_json_payload(): void
    {
        $cashier = $this->createUserWithRole('cashier');

        $response = $this->actingAs($cashier)->postJson(route('customers.store'), [
            'name' => 'Quick Counter Guest',
            'phone' => '+1-555-7777',
            'email' => 'quickguest@example.test',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('customer.name', 'Quick Counter Guest')
            ->assertJsonPath('customer.phone', '+1-555-7777');

        $this->assertDatabaseHas('customers', ['email' => 'quickguest@example.test']);

        // Cashier cannot edit or delete customers
        $customer = Customer::where('email', 'quickguest@example.test')->firstOrFail();
        $this->actingAs($cashier)->get(route('customers.edit', $customer->id))->assertStatus(403);
        $this->actingAs($cashier)->delete(route('customers.destroy', $customer->id))->assertStatus(403);
    }

    public function test_customer_with_existing_orders_cannot_be_force_deleted(): void
    {
        $admin = $this->createUserWithRole('admin');
        $customer = Customer::factory()->create(['created_by' => $admin->id]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->delete(route('customers.destroy', $customer->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
