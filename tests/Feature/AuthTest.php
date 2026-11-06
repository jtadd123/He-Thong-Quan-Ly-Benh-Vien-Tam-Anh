<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\RateLimiter::clear('127.0.0.1');
        User::where('email', 'patient@gmail.com')->update(['is_active' => true]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ĐĂNG NHẬP HỆ THỐNG');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::where('email', 'admin@tamanh.vn')->first();

        $response = $this->post('/login', [
            'email' => 'admin@tamanh.vn',
            'password' => '123456',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_patient_redirects_to_patient_history_on_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'patient@gmail.com',
            'password' => '123456',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.history'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@tamanh.vn',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('email', 'admin@tamanh.vn')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_patient_cannot_access_admin_dashboard(): void
    {
        $patient = User::where('email', 'patient@gmail.com')->first();

        $response = $this->actingAs($patient)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::where('email', 'patient@gmail.com')->first();

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Revert password back for other tests
        $user->password = Hash::make('123456');
        $user->save();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::where('email', 'patient@gmail.com')->first();
        $user->is_active = false;
        $user->save();

        try {
            $response = $this->from('/login')->post('/login', [
                'email' => 'patient@gmail.com',
                'password' => '123456',
            ]);

            $this->assertGuest();
            $response->assertSessionHasErrors('email');
        } finally {
            // Always restore active status even if assertion fails
            $user->is_active = true;
            $user->save();
        }
    }
}
