<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitStatisticRequest;
use App\Models\Department;
use App\Models\ServiceType;
use App\Models\VisitStatistic;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitStatisticController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,staff')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'department_id' => $request->input('department_id'),
            'service_type_id' => $request->input('service_type_id'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
        ];

        $departments = Department::active()->get();
        $serviceTypes = ServiceType::active()->get();

        $statistics = VisitStatistic::with(['department', 'serviceType', 'creator', 'batch'])
            ->filter($filters)
            ->orderByDesc('service_date')
            ->orderByDesc('service_hour')
            ->paginate(20)
            ->withQueryString();

        $totalVisits = (int) VisitStatistic::filter($filters)->sum('visit_count');

        return view('visit_statistics.index', compact('statistics', 'departments', 'serviceTypes', 'filters', 'totalVisits'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $serviceTypes = ServiceType::active()->get();
        $defaultDate = Carbon::today()->format('Y-m-d');
        $defaultHour = (int) Carbon::now()->format('H');

        return view('visit_statistics.create', compact('departments', 'serviceTypes', 'defaultDate', 'defaultHour'));
    }

    public function store(VisitStatisticRequest $request)
    {
        $statistic = VisitStatistic::create([
            'department_id' => $request->department_id,
            'service_type_id' => $request->service_type_id,
            'service_date' => $request->service_date,
            'service_hour' => $request->service_hour,
            'visit_count' => $request->visit_count,
            'source_type' => 'manual',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $statistic->load(['department', 'serviceType']);

        AuditLogService::log(
            action: 'create',
            module: 'visit_statistics',
            description: "บันทึกสถิติใหม่: แผนก {$statistic->department?->name_th} วันที่ {$statistic->service_date->format('Y-m-d')} เวลา {$statistic->formatted_hour} จำนวน {$statistic->visit_count} ครั้ง",
            recordId: $statistic->id,
            newValues: $statistic->toArray()
        );

        return redirect()->route('visit-statistics.index')
            ->with('success', 'บันทึกข้อมูลสถิติการเข้ารับบริการเรียบร้อยแล้ว');
    }

    public function edit(VisitStatistic $visitStatistic)
    {
        $departments = Department::all();
        $serviceTypes = ServiceType::all();

        return view('visit_statistics.edit', compact('visitStatistic', 'departments', 'serviceTypes'));
    }

    public function update(VisitStatisticRequest $request, VisitStatistic $visitStatistic)
    {
        $oldValues = $visitStatistic->toArray();

        $visitStatistic->update([
            'department_id' => $request->department_id,
            'service_type_id' => $request->service_type_id,
            'service_date' => $request->service_date,
            'service_hour' => $request->service_hour,
            'visit_count' => $request->visit_count,
            'notes' => $request->notes,
            'updated_by' => Auth::id(),
        ]);

        $visitStatistic->load(['department', 'serviceType']);

        AuditLogService::log(
            action: 'update',
            module: 'visit_statistics',
            description: "แก้ไขสถิติ: แผนก {$visitStatistic->department?->name_th} วันที่ {$visitStatistic->service_date->format('Y-m-d')} เวลา {$visitStatistic->formatted_hour}",
            recordId: $visitStatistic->id,
            oldValues: $oldValues,
            newValues: $visitStatistic->toArray()
        );

        return redirect()->route('visit-statistics.index')
            ->with('success', 'ปรับปรุงข้อมูลสถิติเรียบร้อยแล้ว');
    }

    public function destroy(VisitStatistic $visitStatistic)
    {
        $desc = "แผนก {$visitStatistic->department?->name_th} วันที่ {$visitStatistic->service_date->format('Y-m-d')} ({$visitStatistic->visit_count} ครั้ง)";
        $oldValues = $visitStatistic->toArray();

        $visitStatistic->delete();

        AuditLogService::log(
            action: 'delete',
            module: 'visit_statistics',
            description: "ลบสถิติการรับบริการ: {$desc}",
            oldValues: $oldValues
        );

        return redirect()->route('visit-statistics.index')
            ->with('success', "ลบรายการสถิติเรียบร้อยแล้ว");
    }
}

