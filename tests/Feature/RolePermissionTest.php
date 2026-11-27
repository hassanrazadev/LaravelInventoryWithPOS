<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
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

    public function test_roles_crud_and_protections(): void
    {
        $admin = $this->createAdmin();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('roles.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('roles.create'))->assertStatus(200);

        $store = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'auditor',
            'permissions' => ['read category', 'read product'],
        ]);

        $store->assertRedirect(route('roles.index'));
        $auditor = Role::where('name', 'auditor')->firstOrFail();
        $this->assertTrue($auditor->hasPermissionTo('read category'));
        $this->assertTrue($auditor->hasPermissionTo('read product'));

        $this->actingAs($admin)->get(route('roles.edit', $auditor->id))->assertStatus(200);

        $this->actingAs($admin)->put(route('roles.update', $auditor->id), [
            'name' => 'senior auditor',
            'permissions' => ['read purchase'],
        ])->assertRedirect(route('roles.index'));

        $auditor->refresh();
        $this->assertSame('senior auditor', $auditor->name);
        $this->assertTrue($auditor->hasPermissionTo('read purchase'));
        $this->assertFalse($auditor->hasPermissionTo('read category'));

        // Cannot rename or delete admin role
        $this->actingAs($admin)->from(route('roles.edit', $adminRole->id))
            ->put(route('roles.update', $adminRole->id), [
                'name' => 'renamed-admin',
                'permissions' => [],
            ])
            ->assertRedirect(route('roles.edit', $adminRole->id))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->delete(route('roles.destroy', $adminRole->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);

        // Cannot delete role assigned to a user
        $assignedUser = User::factory()->create();
        $assignedUser->assignRole($auditor);

        $this->actingAs($admin)->delete(route('roles.destroy', $auditor->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);

        $assignedUser->removeRole($auditor);
        $this->actingAs($admin)->delete(route('roles.destroy', $auditor->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertDatabaseMissing('roles', ['id' => $auditor->id]);
    }

    public function test_permissions_crud(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('permissions.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('permissions.create'))->assertStatus(200);

        $this->actingAs($admin)->post(route('permissions.store'), [
            'name' => 'export report',
        ])->assertRedirect(route('permissions.index'));

        $perm = Permission::where('name', 'export report')->firstOrFail();

        $this->actingAs($admin)->get(route('permissions.edit', $perm->id))->assertStatus(200);

        $this->actingAs($admin)->put(route('permissions.update', $perm->id), [
            'name' => 'download report',
        ])->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', ['id' => $perm->id, 'name' => 'download report']);

        $this->actingAs($admin)->delete(route('permissions.destroy', $perm->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('permissions', ['id' => $perm->id]);
    }

    public function test_rbac_middleware_enforces_permissions_across_controllers(): void
    {
        $this->seed(PermissionsTableSeeder::class);
        $this->seed(RolesTableSeeder::class);

        $unprivileged = User::factory()->create();

        $this->actingAs($unprivileged)->get(route('categories.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('categories.create'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('products.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('purchases.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('purchases.productForm', 0))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('orders.create'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('suppliers.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('customers.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('expenses.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('returns.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('roles.index'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('permissions.index'))->assertStatus(403);

        $unprivileged->givePermissionTo('read category', 'update purchase');

        $this->actingAs($unprivileged)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($unprivileged)->get(route('categories.create'))->assertStatus(403);
        $this->actingAs($unprivileged)->get(route('purchases.productForm', 0))->assertStatus(200);

        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $this->actingAs($manager)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('products.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('purchases.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('suppliers.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('customers.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('expenses.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('returns.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('orders.create'))->assertStatus(200);
        $this->actingAs($manager)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($manager)->get(route('roles.index'))->assertStatus(403);
        $this->actingAs($manager)->get(route('permissions.index'))->assertStatus(403);

        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');

        $this->actingAs($cashier)->get(route('orders.create'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('customers.index'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('suppliers.index'))->assertStatus(403);
        $this->actingAs($cashier)->get(route('expenses.index'))->assertStatus(403);
        $this->actingAs($cashier)->get(route('returns.index'))->assertStatus(403);
    }
}
