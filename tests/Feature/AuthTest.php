<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('user.login'));
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('user.login'));

        $response->assertStatus(200);
        $response->assertSee('Log in');
    }

    public function test_verified_user_can_login_and_view_dashboard(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('admin');

        $response = $this->post(route('user.doLogin'), [
            'email' => 'verified@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertStatus(200);
    }

    public function test_unverified_user_cannot_login(): void
    {
        User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->from(route('user.login'))->post(route('user.doLogin'), [
            'email' => 'unverified@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('user.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_invalid_credentials_fail_and_flash_error(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->from(route('user.login'))->post(route('user.doLogin'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('user.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
