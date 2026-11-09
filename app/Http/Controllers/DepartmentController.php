<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name_th', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $departments = $query->withCount('visitStatistics')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(DepartmentRequest $request)
    {
        $department = Department::create([
            'code' => strtoupper(trim($request->code)),
            'name_th' => trim($request->name_th),
            'name_en' => trim($request->name_en),
            'description' => $request->description,
            'phone' => $request->phone,
            'location' => $request->location,
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(
            action: 'create',
            module: 'departments',
            description: "เพิ่มแผนกใหม่: {$department->code} - {$department->name_th}",
            recordId: $department->id,
            newValues: $department->toArray()
        );

        return redirect()->route('departments.index')
            ->with('success', "เพิ่มแผนก '{$department->name_th}' เรียบร้อยแล้ว");
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $oldValues = $department->toArray();

        $department->update([
            'code' => strtoupper(trim($request->code)),
            'name_th' => trim($request->name_th),
            'name_en' => trim($request->name_en),
            'description' => $request->description,
            'phone' => $request->phone,
            'location' => $request->location,
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(
            action: 'update',
            module: 'departments',
            description: "แก้ไขข้อมูลแผนก: {$department->code} - {$department->name_th}",
            recordId: $department->id,
            oldValues: $oldValues,
            newValues: $department->toArray()
        );

        return redirect()->route('departments.index')
            ->with('success', "ปรับปรุงข้อมูลแผนก '{$department->name_th}' เรียบร้อยแล้ว");
    }

    public function destroy(Department $department)
    {
        if ($department->visitStatistics()->exists()) {
            return back()->with('error', "ไม่สามารถลบแผนก '{$department->name_th}' ได้ เนื่องจากมีข้อมูลสถิติผูกอยู่ (สามารถเลือกปิดสถานะการใช้งานแทนได้)");
        }

        $deptInfo = "{$department->code} - {$department->name_th}";
        $oldValues = $department->toArray();
        $department->delete();

        AuditLogService::log(
            action: 'delete',
            module: 'departments',
            description: "ลบแผนก: {$deptInfo}",
            oldValues: $oldValues
        );

        return redirect()->route('departments.index')
            ->with('success', "ลบแผนก '{$deptInfo}' เรียบร้อยแล้ว");
    }
}

