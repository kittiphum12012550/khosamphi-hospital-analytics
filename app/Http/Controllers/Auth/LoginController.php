<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'บัญชีผู้ใช้นี้ถูกปิดใช้งานชั่วคราว กรุณาติดต่อผู้ดูแลระบบโรงพยาบาล',
                ]);
            }

            // Update login metadata
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $request->session()->regenerate();

            // Audit Log
            AuditLogService::log(
                action: 'login',
                module: 'auth',
                description: "ผู้ใช้งาน {$user->name} ({$user->email}) เข้าสู่ระบบสำเร็จ",
                recordId: $user->id
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', "ยินดีต้อนรับ {$user->name} เข้าสู่ระบบ Khosamphi Hospital Analytics");
        }

        throw ValidationException::withMessages([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuditLogService::log(
                action: 'logout',
                module: 'auth',
                description: "ผู้ใช้งาน {$user->name} ออกจากระบบ",
                recordId: $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'ท่านได้ออกจากระบบเรียบร้อยแล้ว');
    }
}

