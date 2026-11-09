<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportPreviewRequest;
use App\Models\ImportBatch;
use App\Services\ImportValidationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImportController extends Controller
{
    protected ImportValidationService $importService;

    public function __construct(ImportValidationService $importService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin,staff');
        $this->importService = $importService;
    }

    /**
     * Step 1: Upload Form
     */
    public function index()
    {
        $recentBatches = ImportBatch::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('imports.index', compact('recentBatches'));
    }

    /**
     * Steps 2, 3, 4: Validate and Show Preview
     */
    public function preview(ImportPreviewRequest $request)
    {
        try {
            $previewData = $this->importService->previewFile($request->file('file'));
            return view('imports.preview', compact('previewData'));
        } catch (Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการตรวจสอบไฟล์: ' . $e->getMessage());
        }
    }

    /**
     * Steps 6, 7, 8: Confirm and Execute Database Transaction
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'original_filename' => 'required|string',
            'file_hash' => 'required|string',
        ]);

        try {
            $batch = $this->importService->executeImport(
                tempPath: $request->temp_path,
                originalFilename: $request->original_filename,
                fileHash: $request->file_hash
            );

            return redirect()->route('imports.batches')
                ->with('success', "นำเข้าข้อมูลชุด '{$batch->batch_code}' สำเร็จ จำนวน {$batch->success_rows} แถว (ข้อผิดพลาด {$batch->error_rows} แถว)");
        } catch (Exception $e) {
            return redirect()->route('imports.index')
                ->with('error', 'การนำเข้าข้อมูลล้มเหลว: ' . $e->getMessage());
        }
    }

    /**
     * View history of all import batches
     */
    public function batches(Request $request)
    {
        $query = ImportBatch::with(['user', 'rollbackUser']);

        // Staff can only see their own batches if not admin
        if (!auth()->user()->isAdmin()) {
            $query->where('imported_by', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->latest()->paginate(15)->withQueryString();

        return view('imports.batches', compact('batches'));
    }

    /**
     * Rollback a specific batch
     */
    public function rollback(ImportBatch $batch)
    {
        // Only admin or the staff member who imported it can rollback
        if (!auth()->user()->isAdmin() && $batch->imported_by !== auth()->id()) {
            abort(403, 'คุณไม่มีสิทธิ์ยกเลิกชุดข้อมูลของผู้อื่น');
        }

        try {
            $this->importService->rollbackBatch($batch);
            return back()->with('success', "ยกเลิกและย้อนกลับชุดข้อมูล {$batch->batch_code} เรียบร้อยแล้ว");
        } catch (Exception $e) {
            return back()->with('error', 'ไม่สามารถย้อนกลับชุดข้อมูลได้: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV import template
     */
    public function downloadTemplate(string $format = 'csv')
    {
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel Thai compatibility
        $csvContent .= "service_date,department_code,service_type_code,service_hour,visit_count,notes\n";
        $csvContent .= "2024-11-01,OPD,GEN_EXAM,8,25,ตรวจโรคทั่วไปช่วงเช้า\n";
        $csvContent .= "2024-11-01,OPD,GEN_EXAM,9,38,ช่วงเวลาหนาแน่น\n";
        $csvContent .= "2024-11-01,ER,EMERGENCY,10,12,อุบัติเหตุจราจร\n";
        $csvContent .= "2024-11-01,DENT,DENTAL_CARE,9,8,ขูดหินปูนและอุดฟัน\n";
        $csvContent .= "2024-11-01,PT,REHAB,13,6,กายภาพบำบัดฟื้นฟูกล้ามเนื้อ\n";
        $csvContent .= "2024-11-01,TTM,THAI_MED,14,7,นวดประคบสมุนไพร\n";
        $csvContent .= "2024-11-01,LAB,LAB_TEST,8,45,เจาะเลือดตรวจเบาหวาน\n";
        $csvContent .= "2024-11-01,PHARM,DISPENSE,9,40,จ่ายยาผู้ป่วยนอก\n";

        $filename = "khosamphi_visit_template.csv";

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

