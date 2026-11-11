<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\VisitStatistic;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('role', 'viewer')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('แดชบอร์ดวิเคราะห์สถิติการเข้ารับบริการ');
        $response->assertSee('usersByDepartmentChart');
    }

    public function test_analytics_json_api_returns_valid_structure(): void
    {
        $user = User::where('role', 'staff')->first();

        $response = $this->actingAs($user)->getJson('/dashboard/analytics-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => [
                'total_visits',
                'today_visits',
                'this_month_visits',
                'top_department_name',
                'top_department_count',
                'active_departments',
                'avg_daily_visits',
            ],
            'daily_trend' => ['labels', 'data'],
            'department_breakdown' => ['labels', 'data'],
            'users_by_department' => ['labels', 'data'],
            'service_distribution' => ['labels', 'data'],
            'monthly_comparison' => ['labels', 'current_year', 'current_data', 'previous_year', 'previous_data'],
            'hourly_distribution' => ['labels', 'data'],
        ]);
    }

    public function test_dashboard_reports_user_counts_by_department_including_unassigned_users(): void
    {
        User::create([
            'name' => 'ผู้ใช้แผนกทดสอบ 1',
            'email' => 'department-one@khosamphi.go.th',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'department_name' => 'แผนกทดสอบ',
            'is_active' => true,
        ]);
        User::create([
            'name' => 'ผู้ใช้แผนกทดสอบ 2',
            'email' => 'department-two@khosamphi.go.th',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'department_name' => 'แผนกทดสอบ',
            'is_active' => false,
        ]);
        User::create([
            'name' => 'ผู้ใช้ไม่ระบุแผนก',
            'email' => 'no-department@khosamphi.go.th',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'department_name' => ' ',
            'is_active' => true,
        ]);

        $response = $this->actingAs(User::where('role', 'staff')->first())
            ->getJson('/dashboard/analytics-data');

        $response->assertOk();
        $userCounts = $response->json('users_by_department');

        $departmentIndex = array_search('แผนกทดสอบ', $userCounts['labels'], true);
        $unassignedIndex = array_search('ไม่ระบุแผนก', $userCounts['labels'], true);

        $this->assertNotFalse($departmentIndex);
        $this->assertNotFalse($unassignedIndex);
        $this->assertSame(2, $userCounts['data'][$departmentIndex]);
        $this->assertSame(1, $userCounts['data'][$unassignedIndex]);
    }

    public function test_viewer_cannot_access_admin_departments_crud(): void
    {
        $viewer = User::where('role', 'viewer')->first();

        $response = $this->actingAs($viewer)->get('/departments/create');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_departments_crud(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/departments');
        $response->assertStatus(200);
        $response->assertSee('จัดการแผนก');
    }
}
