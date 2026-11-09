<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(UserRequest $request)
    {
        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_name' => trim($request->department_name),
            'phone' => trim($request->phone),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(
            action: 'create',
            module: 'users',
            description: "สร้างผู้ใช้งานใหม่: {$user->name} ({$user->email}) ระดับสิทธิ์: {$user->role}",
            recordId: $user->id,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department_name' => $user->department_name,
            ]
        );

        return redirect()->route('users.index')
            ->with('success', "เพิ่มผู้ใช้งาน '{$user->name}' เรียบร้อยแล้ว");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'department_name' => $user->department_name,
            'is_active' => $user->is_active,
        ];

        $updateData = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'role' => $request->role,
            'department_name' => trim($request->department_name),
            'phone' => trim($request->phone),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        AuditLogService::log(
            action: 'update',
            module: 'users',
            description: "แก้ไขข้อมูลผู้ใช้งาน: {$user->name} ({$user->email})",
            recordId: $user->id,
            oldValues: $oldValues,
            newValues: $updateData
        );

        return redirect()->route('users.index')
            ->with('success', "ปรับปรุงข้อมูลผู้ใช้งาน '{$user->name}' เรียบร้อยแล้ว");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'คุณไม่สามารถลบบัญชีของตนเองได้');
        }

        $info = "{$user->name} ({$user->email})";
        $oldValues = $user->toArray();
        $user->delete();

        AuditLogService::log(
            action: 'delete',
            module: 'users',
            description: "ลบผู้ใช้งาน: {$info}",
            oldValues: $oldValues
        );

        return redirect()->route('users.index')
            ->with('success', "ลบผู้ใช้งาน '{$info}' เรียบร้อยแล้ว");
    }
}

