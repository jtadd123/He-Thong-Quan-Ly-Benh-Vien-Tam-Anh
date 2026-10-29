<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_admin_can_view_user_list(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Quản lý Người dùng');
    }

    public function test_non_admin_cannot_view_user_list(): void
    {
        $patient = User::where('role', User::ROLE_PATIENT)->first();

        $response = $this->actingAs($patient)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_new_user(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $email = 'testuser_' . time() . '_' . rand(100000, 999999) . '@tamanh.vn';

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Nhân viên Mới',
            'email' => $email,
            'password' => 'secret123',
            'phone' => '0911223344',
            'role' => User::ROLE_RECEPTIONIST,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => User::ROLE_RECEPTIONIST,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $response = $this->actingAs($admin)->delete('/admin/users/' . $admin->id);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_demote_themselves_from_admin_role(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $response = $this->actingAs($admin)->put('/admin/users/' . $admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'role' => User::ROLE_PATIENT,
        ]);

        $response->assertSessionHas('error');
        $admin->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $admin->role);
    }

    public function test_cannot_delete_doctor_user_with_linked_appointments(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $doctorUser = User::where('role', User::ROLE_DOCTOR)
            ->whereHas('doctor.appointments')
            ->first();

        if ($doctorUser) {
            $response = $this->actingAs($admin)->delete('/admin/users/' . $doctorUser->id);

            $response->assertSessionHas('error');
            $this->assertDatabaseHas('users', ['id' => $doctorUser->id]);
        }
    }
}
