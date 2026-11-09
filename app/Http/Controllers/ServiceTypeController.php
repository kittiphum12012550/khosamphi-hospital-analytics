<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceTypeRequest;
use App\Models\ServiceType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = ServiceType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $serviceTypes = $query->withCount('visitStatistics')
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('service_types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('service_types.create');
    }

    public function store(ServiceTypeRequest $request)
    {
        $serviceType = ServiceType::create([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'category' => trim($request->category),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(
            action: 'create',
            module: 'service_types',
            description: "เพิ่มประเภทบริการ: {$serviceType->code} - {$serviceType->name}",
            recordId: $serviceType->id,
            newValues: $serviceType->toArray()
        );

        return redirect()->route('service-types.index')
            ->with('success', "เพิ่มประเภทบริการ '{$serviceType->name}' เรียบร้อยแล้ว");
    }

    public function edit(ServiceType $serviceType)
    {
        return view('service_types.edit', compact('serviceType'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $serviceType)
    {
        $oldValues = $serviceType->toArray();

        $serviceType->update([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'category' => trim($request->category),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(
            action: 'update',
            module: 'service_types',
            description: "แก้ไขประเภทบริการ: {$serviceType->code} - {$serviceType->name}",
            recordId: $serviceType->id,
            oldValues: $oldValues,
            newValues: $serviceType->toArray()
        );

        return redirect()->route('service-types.index')
            ->with('success', "ปรับปรุงประเภทบริการ '{$serviceType->name}' เรียบร้อยแล้ว");
    }

    public function destroy(ServiceType $serviceType)
    {
        if ($serviceType->visitStatistics()->exists()) {
            return back()->with('error', "ไม่สามารถลบประเภทบริการ '{$serviceType->name}' ได้ เนื่องจากมีสถิติการใช้งานอ้างอิงอยู่ (สามารถปิดการใช้งานแทนได้)");
        }

        $info = "{$serviceType->code} - {$serviceType->name}";
        $oldValues = $serviceType->toArray();
        $serviceType->delete();

        AuditLogService::log(
            action: 'delete',
            module: 'service_types',
            description: "ลบประเภทบริการ: {$info}",
            oldValues: $oldValues
        );

        return redirect()->route('service-types.index')
            ->with('success', "ลบประเภทบริการ '{$info}' เรียบร้อยแล้ว");
    }
}

