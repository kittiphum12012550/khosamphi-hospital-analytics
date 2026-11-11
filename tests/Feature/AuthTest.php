<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('โรงพยาบาลโกสัมพีนคร');
    }

    public function test_login_form_uses_https_when_behind_a_tls_terminating_proxy(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'hospital.example.com',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/login');

        $response->assertOk()
            ->assertSee('action="https://hospital.example.com/login"', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::create([
            'name' => 'นายแพทย์ทดสอบ โกสัมพี',
            'email' => 'doctor@khosamphi.go.th',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'doctor@khosamphi.go.th',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');

        // Verify Audit Log is recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'user_id' => $user->id,
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'เจ้าหน้าที่ทดสอบ',
            'email' => 'staff@khosamphi.go.th',
            'password' => Hash::make('correct_password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'staff@khosamphi.go.th',
            'password' => 'wrong_password',
        ]);

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'ผู้ใช้ที่ถูกระงับ',
            'email' => 'inactive@khosamphi.go.th',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@khosamphi.go.th',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }
}
