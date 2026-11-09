<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $recentLogs = AuditLog::where('user_id', $user->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('profile.index', compact('user', 'recentLogs'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:191',
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:50',
            'department_name' => 'nullable|string|max:100',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'current_password.required_with' => 'กรุณากรอกรหัสผ่านปัจจุบันหากต้องการเปลี่ยนรหัสผ่าน',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร',
            'new_password.confirmed' => 'การยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = trim($request->name);
        $user->email = strtolower(trim($request->email));
        $user->phone = trim($request->phone);
        $user->department_name = trim($request->department_name);
        $user->save();

        AuditLogService::log(
            action: 'update',
            module: 'profile',
            description: "ผู้ใช้งาน {$user->name} อัปเดตข้อมูลโปรไฟล์ส่วนตัว",
            recordId: $user->id
        );

        return back()->with('success', 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว');
    }
}

