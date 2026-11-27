<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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

    public function test_supplier_crud_and_soft_delete_flow(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('suppliers.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('suppliers.create'))->assertStatus(200);

        $store = $this->actingAs($admin)->post(route('suppliers.store'), [
            'name' => 'Apex Electronics Supply',
            'email' => 'sales@apexsupply.test',
            'phone' => '+1-555-2020',
            'address' => '400 Industrial Pkwy',
        ]);

        $store->assertRedirect(route('suppliers.index'));
        $supplier = Supplier::where('email', 'sales@apexsupply.test')->firstOrFail();

        $this->actingAs($admin)->get(route('suppliers.show', $supplier->id))
            ->assertStatus(200)
            ->assertSee('Apex Electronics Supply');

        $this->actingAs($admin)->get(route('suppliers.edit', $supplier->id))
            ->assertStatus(200)
            ->assertSee('Apex Electronics Supply');

        $update = $this->actingAs($admin)->put(route('suppliers.update', $supplier->id), [
            'name' => 'Apex Supply Group',
            'email' => 'sales@apexsupply.test',
            'phone' => '+1-555-3030',
            'address' => '500 Commerce Dr',
        ]);

        $update->assertRedirect(route('suppliers.index'));
        $supplier->refresh();
        $this->assertSame('Apex Supply Group', $supplier->name);
        $this->assertSame('+1-555-3030', $supplier->phone);

        // Soft delete and restore toggle
        $this->actingAs($admin)->patch(route('suppliers.delete', $supplier->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

        $this->actingAs($admin)->patch(route('suppliers.delete', $supplier->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'deleted_at' => null]);

        // Permanent delete succeeds when no purchases exist
        $this->actingAs($admin)->delete(route('suppliers.destroy', $supplier->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_with_existing_purchases_cannot_be_force_deleted(): void
    {
        $admin = $this->createAdmin();
        $supplier = Supplier::factory()->create(['created_by' => $admin->id]);
        Purchase::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->delete(route('suppliers.destroy', $supplier->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }
}
