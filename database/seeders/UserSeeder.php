<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@khosamphi.go.th'],
            [
                'name' => 'ผู้ดูแลระบบ รพ.โกสัมพีนคร (Administrator)',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'department_name' => 'ศูนย์ข้อมูลและสารสนเทศทางการแพทย์',
                'phone' => '055-741-690',
                'is_active' => true,
            ]
        );

        // 2. Staff
        User::updateOrCreate(
            ['email' => 'staff@khosamphi.go.th'],
            [
                'name' => 'เจ้าหน้าที่เวชระเบียนและสถิติ (Medical Records Staff)',
                'password' => Hash::make('staff1234'),
                'role' => 'staff',
                'department_name' => 'งานเวชระเบียนและสถิติ',
                'phone' => '055-741-691',
                'is_active' => true,
            ]
        );

        // 3. Viewer
        User::updateOrCreate(
            ['email' => 'viewer@khosamphi.go.th'],
            [
                'name' => 'ผู้อำนวยการและคณะกรรมการบริหาร (Hospital Executive/Viewer)',
                'password' => Hash::make('viewer1234'),
                'role' => 'viewer',
                'department_name' => 'กลุ่มงานบริหารและวางแผนยุทธศาสตร์',
                'phone' => '055-741-692',
                'is_active' => true,
            ]
        );
    }
}

