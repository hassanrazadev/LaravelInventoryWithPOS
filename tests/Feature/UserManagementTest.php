<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_users_crud_and_password_preservation(): void
    {
        $admin = $this->createAdmin();
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        $this->actingAs($admin)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.create'))->assertStatus(200);

        $store = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Jane Cashier',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'roles' => [$cashierRole->name],
            'email_verified' => '1',
        ]);

        $store->assertRedirect(route('users.index'));
        $created = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertTrue($created->hasRole('cashier'));
        $this->assertNotNull($created->email_verified_at);
        $this->assertTrue(Hash::check('secret123', $created->password));

        $this->actingAs($admin)->get(route('users.show', $created->id))
            ->assertStatus(200)
            ->assertSee('Jane Cashier');

        $this->actingAs($admin)->get(route('users.edit', $created->id))
            ->assertStatus(200)
            ->assertSee('Jane Cashier');

        // Update without password keeps existing password
        $update = $this->actingAs($admin)->put(route('users.update', $created->id), [
            'name' => 'Jane Manager',
            'email' => 'jane.mgr@example.com',
            'password' => '',
            'password_confirmation' => '',
            'roles' => [$managerRole->name],
            'email_verified' => '1',
        ]);

        $update->assertRedirect(route('users.index'));
        $created->refresh();
        $this->assertSame('Jane Manager', $created->name);
        $this->assertSame('jane.mgr@example.com', $created->email);
        $this->assertTrue($created->hasRole('manager'));
        $this->assertFalse($created->hasRole('cashier'));
        $this->assertTrue(Hash::check('secret123', $created->password));
    }

    public function test_user_soft_delete_restore_and_force_destroy(): void
    {
        $admin = $this->createAdmin();
        $target = User::factory()->create(['created_by' => null]);

        $softDelete = $this->actingAs($admin)->patch(route('users.delete', $target->id));
        $softDelete->assertStatus(200)->assertJson(['status' => true]);
        $this->assertSoftDeleted('users', ['id' => $target->id]);

        $restore = $this->actingAs($admin)->patch(route('users.delete', $target->id));
        $restore->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'deleted_at' => null]);

        $destroy = $this->actingAs($admin)->delete(route('users.destroy', $target->id));
        $destroy->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_self_or_remove_last_admin_role(): void
    {
        $admin = $this->createAdmin();
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $this->actingAs($admin)->patch(route('users.delete', $admin->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);

        $this->actingAs($admin)->delete(route('users.destroy', $admin->id))
            ->assertStatus(200)
            ->assertJson(['status' => false]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->actingAs($admin)->from(route('users.edit', $admin->id))
            ->put(route('users.update', $admin->id), [
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => [$cashierRole->name],
                'email_verified' => '1',
            ])
            ->assertRedirect(route('users.edit', $admin->id))
            ->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }
}
