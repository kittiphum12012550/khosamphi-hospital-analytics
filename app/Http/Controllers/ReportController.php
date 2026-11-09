<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ServiceType;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware('auth');
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $filters = [
            'report_type' => $request->input('report_type', 'daily'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'department_id' => $request->input('department_id'),
            'service_type_id' => $request->input('service_type_id'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
        ];

        $departments = Department::active()->get();
        $serviceTypes = ServiceType::active()->get();

        $reportData = $this->reportService->generateReport($filters);

        return view('reports.index', compact('reportData', 'departments', 'serviceTypes', 'filters'));
    }

    /**
     * Export report data as Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->all();
        $reportData = $this->reportService->generateReport($filters);

        $reportType = $filters['report_type'] ?? 'daily';
        $filename = "khosamphi_report_{$reportType}_" . date('Ymd_His') . ".csv";

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM for Thai support in Microsoft Excel
        $output .= "รายงานสถิติการเข้ารับบริการ " . config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') . "\n";
        $output .= "ประเภทรายงาน: " . strtoupper($reportType) . "\n";
        $output .= "สร้างเมื่อ: " . $reportData['generated_at'] . " โดย " . auth()->user()->name . "\n\n";

        switch ($reportType) {
            case 'monthly':
                $output .= "ช่วงเวลา/เดือน,จำนวนวันให้บริการ,จำนวนแผนกที่ให้บริการ,จำนวนครั้งการรับบริการ (Visits),เฉลี่ยต่อวัน\n";
                foreach ($reportData['items'] as $item) {
                    $output .= "\"{$item->period_label}\",{$item->active_days},{$item->dept_count},{$item->total_visits},{$item->avg_daily}\n";
                }
                break;
            case 'yearly':
                $output .= "ปี,จำนวนวันให้บริการ,จำนวนครั้งการรับบริการ (Visits),เฉลี่ยต่อวัน\n";
                foreach ($reportData['items'] as $item) {
                    $output .= "\"{$item->period_label}\",{$item->active_days},{$item->total_visits},{$item->avg_daily}\n";
                }
                break;
            case 'department':
                $output .= "รหัสแผนก,ชื่อแผนก,จำนวนวันที่มีผู้รับบริการ,จำนวนครั้งรวม (Visits)\n";
                foreach ($reportData['items'] as $item) {
                    $output .= "\"{$item->dept_code}\",\"{$item->dept_name}\",{$item->active_days},{$item->total_visits}\n";
                }
                break;
            case 'service_type':
                $output .= "รหัสบริการ,ชื่อประเภทบริการ,หมวดหมู่,จำนวนครั้งรวม (Visits)\n";
                foreach ($reportData['items'] as $item) {
                    $output .= "\"{$item->st_code}\",\"{$item->st_name}\",\"{$item->st_category}\",{$item->total_visits}\n";
                }
                break;
            default:
                $output .= "วันที่ให้บริการ,จำนวนแผนกที่ให้บริการ,จำนวนบริการ,จำนวนครั้งรวม (Visits)\n";
                foreach ($reportData['items'] as $item) {
                    $output .= "\"{$item->period_label}\",{$item->dept_count},{$item->service_count},{$item->total_visits}\n";
                }
                break;
        }

        $output .= "\nยอดรวมทั้งสิ้น,,,,{$reportData['grand_total']} ครั้ง\n";

        AuditLogService::log(
            action: 'export',
            module: 'reports',
            description: "ส่งออกรายงาน Excel ({$reportType}) จำนวน {$reportData['grand_total']} visits"
        );

        return Response::make($output, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export report data as PDF using DomPDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->all();
        $reportData = $this->reportService->generateReport($filters);

        AuditLogService::log(
            action: 'export',
            module: 'reports',
            description: "ส่งออกรายงาน PDF ({$reportData['report_type']})"
        );

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = Pdf::loadView('reports.pdf', compact('reportData'))
                ->setPaper('a4', 'portrait');
            return $pdf->download("khosamphi_report_" . date('Ymd_His') . ".pdf");
        }

        // Fallback to print view if PDF extension is not enabled
        return view('reports.pdf', compact('reportData'));
    }

    /**
     * Print View
     */
    public function print(Request $request)
    {
        $filters = $request->all();
        $reportData = $this->reportService->generateReport($filters);

        return view('reports.pdf', compact('reportData'));
    }
}

