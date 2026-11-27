<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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
        $response->assertSee('Welcome back');
    }

    public function test_verified_user_can_login_with_remember_and_view_dashboard(): void
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
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->getRememberToken());

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertStatus(200);
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.login'))
            ->assertRedirect('/');
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

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('user.logout'));

        $response->assertRedirect(route('user.login'));
        $this->assertGuest();
    }

    public function test_forgot_and_reset_password_flow(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'resetme@example.com',
            'password' => bcrypt('oldpass123'),
            'email_verified_at' => now(),
        ]);

        $this->get(route('password.request'))->assertStatus(200);

        $this->post(route('password.email'), ['email' => 'resetme@example.com'])
            ->assertRedirect();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertStatus(200);

        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $resetResponse->assertRedirect(route('user.login'));
        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_profile_can_be_viewed_and_updated_including_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Initial Name',
            'email' => 'profile@example.com',
            'password' => bcrypt('oldpass123'),
        ]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertSee('Initial Name');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@example.com', $user->fresh()->email);

        $this->actingAs($user)->from(route('profile.edit'))
            ->put(route('profile.password'), [
                'current_password' => 'wrong-current',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'oldpass123',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }
}
