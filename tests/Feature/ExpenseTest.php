<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
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

    public function test_expense_crud_filter_and_soft_delete_flow(): void
    {
        $manager = $this->createUserWithRole('manager');

        $this->actingAs($manager)->get(route('expenses.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('expenses.create'))->assertStatus(200);

        $this->actingAs($manager)->post(route('expenses.store'), [
            'expense_name' => 'Warehouse Maintenance',
            'category' => 'Maintenance',
            'expense_amount' => 350.75,
            'expense_date' => now()->toDateString(),
            'notes' => 'HVAC filter replacement',
        ])->assertRedirect(route('expenses.index'));

        Expense::factory()->create([
            'expense_name' => 'Monthly Rent',
            'category' => 'Rent',
            'expense_amount' => 900.00,
            'created_by' => $manager->id,
        ]);

        $expense = Expense::where('expense_name', 'Warehouse Maintenance')->firstOrFail();

        $this->actingAs($manager)->get(route('expenses.index', ['category' => 'Maintenance']))
            ->assertStatus(200)
            ->assertSee('Warehouse Maintenance')
            ->assertDontSee('Monthly Rent');

        $this->actingAs($manager)->get(route('expenses.show', $expense->id))
            ->assertStatus(200)
            ->assertSee('Warehouse Maintenance')
            ->assertSee('350.75');

        $this->actingAs($manager)->get(route('expenses.edit', $expense->id))
            ->assertStatus(200)
            ->assertSee('Warehouse Maintenance');

        $this->actingAs($manager)->put(route('expenses.update', $expense->id), [
            'expense_name' => 'Warehouse HVAC Service',
            'category' => 'Maintenance',
            'expense_amount' => 420.00,
            'expense_date' => now()->toDateString(),
            'notes' => 'Full annual servicing',
        ])->assertRedirect(route('expenses.index'));

        $expense->refresh();
        $this->assertSame('Warehouse HVAC Service', $expense->expense_name);
        $this->assertEquals(420.00, (float) $expense->expense_amount);

        // Soft delete and restore toggle
        $this->actingAs($manager)->patch(route('expenses.delete', $expense->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);

        $this->actingAs($manager)->patch(route('expenses.delete', $expense->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'deleted_at' => null]);

        // Permanent delete
        $this->actingAs($manager)->delete(route('expenses.destroy', $expense->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_validation_and_rbac_enforcement(): void
    {
        $admin = $this->createUserWithRole('admin');
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');

        $this->actingAs($cashier)->get(route('expenses.index'))->assertStatus(403);
        $this->actingAs($cashier)->get(route('expenses.create'))->assertStatus(403);

        $this->actingAs($admin)->from(route('expenses.create'))
            ->post(route('expenses.store'), [
                'expense_name' => '',
                'expense_amount' => 0,
            ])
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHasErrors(['expense_name', 'expense_amount']);
    }
}
