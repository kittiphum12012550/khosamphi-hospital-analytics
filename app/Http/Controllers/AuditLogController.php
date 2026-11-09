<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $filters = [
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'module' => $request->input('module'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'search' => $request->input('search'),
        ];

        $users = User::orderBy('name')->get();

        $actions = [
            'login' => 'เข้าสู่ระบบ (Login)',
            'logout' => 'ออกจากระบบ (Logout)',
            'create' => 'เพิ่มข้อมูล (Create)',
            'update' => 'แก้ไขข้อมูล (Update)',
            'delete' => 'ลบข้อมูล (Delete)',
            'import' => 'นำเข้าไฟล์ (Import)',
            'export' => 'ส่งออกรายงาน (Export)',
            'rollback' => 'ยกเลิกชุดข้อมูล (Rollback)',
        ];

        $modules = [
            'auth' => 'ระบบเข้าสู่ระบบ (Auth)',
            'departments' => 'จัดการแผนก',
            'service_types' => 'ประเภทบริการ',
            'visit_statistics' => 'ข้อมูลสถิติการรับบริการ',
            'imports' => 'การนำเข้าไฟล์',
            'reports' => 'รายงาน',
            'users' => 'จัดการผู้ใช้งาน',
            'profile' => 'โปรไฟล์',
        ];

        $logs = AuditLog::with('user')
            ->filter($filters)
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('audit_logs.index', compact('logs', 'users', 'actions', 'modules', 'filters'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return response()->json($auditLog);
    }
}

