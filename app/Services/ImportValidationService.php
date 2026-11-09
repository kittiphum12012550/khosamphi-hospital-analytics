<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ImportBatch;
use App\Models\ServiceType;
use App\Models\VisitStatistic;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportValidationService
{
    /**
     * Parse and inspect the uploaded file, returning preview and error stats
     */
    public function previewFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $hash = hash_file('sha256', $file->getRealPath());

        // Check if identical file was already imported
        $existingBatch = ImportBatch::where('file_hash', $hash)
            ->where('status', 'completed')
            ->first();

        // Read rows from CSV or Excel
        $rawRows = $this->extractRawRows($file, $extension);

        if (empty($rawRows)) {
            throw new Exception('ไม่พบข้อมูลในไฟล์ หรือรูปแบบหัวตารางไม่ถูกต้อง');
        }

        // Cache departments & service types for fast in-memory validation
        $departmentsByCode = Department::pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtoupper(trim($code)) => $id])->toArray();
        $departmentsByName = Department::pluck('id', 'name_th')->mapWithKeys(fn($id, $name) => [trim($name) => $id])->toArray();

        $serviceTypesByCode = ServiceType::pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtoupper(trim($code)) => $id])->toArray();
        $serviceTypesByName = ServiceType::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [trim($name) => $id])->toArray();

        $validRows = [];
        $errorRows = [];
        $previewList = [];
        $seenSlots = [];

        foreach ($rawRows as $index => $row) {
            $rowNum = $index + 2; // Row 1 is header, so data starts at row 2
            $errors = [];

            // 1. Validate Date
            $rawDate = trim($row['date'] ?? '');
            $parsedDate = $this->parseDate($rawDate);
            if (!$parsedDate) {
                $errors[] = "วันที่ไม่ถูกต้อง ('{$rawDate}') ต้องเป็น YYYY-MM-DD หรือ DD/MM/YYYY";
            }

            // 2. Validate Department
            $rawDept = trim($row['department'] ?? '');
            $deptUpper = strtoupper($rawDept);
            $departmentId = $departmentsByCode[$deptUpper] ?? $departmentsByName[$rawDept] ?? null;
            if (!$departmentId) {
                $errors[] = "ไม่พบแผนก '{$rawDept}' ในระบบ (กรุณาใช้รหัส เช่น OPD หรือชื่อภาษาไทย)";
            }

            // 3. Validate Service Type
            $rawService = trim($row['service_type'] ?? '');
            $serviceUpper = strtoupper($rawService);
            $serviceTypeId = $serviceTypesByCode[$serviceUpper] ?? $serviceTypesByName[$rawService] ?? null;
            if (!$serviceTypeId) {
                $errors[] = "ไม่พบประเภทบริการ '{$rawService}' ในระบบ";
            }

            // 4. Validate Hour (0-23)
            $rawHour = trim($row['hour'] ?? '8');
            $hour = is_numeric($rawHour) ? (int)$rawHour : -1;
            if ($hour < 0 || $hour > 23) {
                $errors[] = "ชั่วโมงการให้บริการต้องเป็น 0 ถึง 23 (ค่าที่พบ: '{$rawHour}')";
            }

            // 5. Validate Visit Count (integer >= 0)
            $rawCount = trim($row['visit_count'] ?? '1');
            $visitCount = is_numeric($rawCount) ? (int)$rawCount : -1;
            if ($visitCount < 0) {
                $errors[] = "จำนวนครั้ง (Visits) ต้องเป็นตัวเลขจำนวนเต็มตั้งแต่ 0 ขึ้นไป (ค่าที่พบ: '{$rawCount}')";
            }

            // 6. Check duplicate slot within the same batch
            if ($parsedDate && $departmentId && $serviceTypeId && $hour >= 0) {
                $slotKey = "{$parsedDate}_{$departmentId}_{$serviceTypeId}_{$hour}";
                if (isset($seenSlots[$slotKey])) {
                    $errors[] = "ข้อมูลซ้ำกับแถวที่ {$seenSlots[$slotKey]} (วันที่, แผนก, บริการ, และชั่วโมงเดียวกัน)";
                } else {
                    $seenSlots[$slotKey] = $rowNum;
                }
            }

            $rowData = [
                'row_number' => $rowNum,
                'date' => $parsedDate ?: $rawDate,
                'raw_date' => $rawDate,
                'department_id' => $departmentId,
                'department_name' => $rawDept,
                'service_type_id' => $serviceTypeId,
                'service_type_name' => $rawService,
                'hour' => $hour >= 0 ? $hour : $rawHour,
                'visit_count' => $visitCount >= 0 ? $visitCount : $rawCount,
                'notes' => trim($row['notes'] ?? ''),
                'errors' => $errors,
                'is_valid' => empty($errors),
            ];

            if (empty($errors)) {
                $validRows[] = $rowData;
            } else {
                $errorRows[] = $rowData;
            }

            // Keep up to 30 rows for table preview
            if (count($previewList) < 30) {
                $previewList[] = $rowData;
            }
        }

        // Store file temporarily for confirmation step
        $tempPath = $file->store('temp_imports');

        return [
            'temp_path' => $tempPath,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_hash' => $hash,
            'file_type' => $extension,
            'is_duplicate_file' => $existingBatch ? true : false,
            'existing_batch_code' => $existingBatch?->batch_code,
            'total_rows' => count($rawRows),
            'valid_count' => count($validRows),
            'error_count' => count($errorRows),
            'can_import' => count($validRows) > 0,
            'preview_rows' => $previewList,
            'error_summary' => array_slice($errorRows, 0, 50),
        ];
    }

    /**
     * Execute full import into database with transaction and batch tracking
     */
    public function executeImport(string $tempPath, string $originalFilename, string $fileHash, array $options = []): ImportBatch
    {
        $fullPath = storage_path('app/' . $tempPath);
        if (!file_exists($fullPath)) {
            throw new Exception('ไม่พบไฟล์ชั่วคราวสำหรับการนำเข้า กรุณาอัปโหลดไฟล์ใหม่อีกครั้ง');
        }

        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $dummyFile = new UploadedFile($fullPath, $originalFilename, null, null, true);
        $preview = $this->previewFile($dummyFile);

        $userId = Auth::id() ?? 1;
        $batchCode = 'BATCH-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

        return DB::transaction(function () use ($tempPath, $originalFilename, $fileHash, $extension, $userId, $batchCode, $preview) {
            // Create batch record
            $batch = ImportBatch::create([
                'batch_code' => $batchCode,
                'original_filename' => $originalFilename,
                'file_path' => $tempPath,
                'file_hash' => $fileHash,
                'file_type' => $extension,
                'file_size_bytes' => filesize(storage_path('app/' . $tempPath)),
                'total_rows' => $preview['total_rows'],
                'success_rows' => 0,
                'error_rows' => $preview['error_count'],
                'status' => 'processing',
                'error_summary' => $preview['error_summary'],
                'imported_by' => $userId,
            ]);

            $insertedCount = 0;
            $chunks = array_chunk($preview['preview_rows'], 500);

            // Re-read all raw rows to get valid items
            $dummyFile = new UploadedFile(storage_path('app/' . $tempPath), $originalFilename, null, null, true);
            $departments = Department::pluck('id', 'code')->mapWithKeys(fn($id, $c) => [strtoupper(trim($c)) => $id])
                ->merge(Department::pluck('id', 'name_th')->mapWithKeys(fn($id, $n) => [trim($n) => $id]))
                ->toArray();
            $serviceTypes = ServiceType::pluck('id', 'code')->mapWithKeys(fn($id, $c) => [strtoupper(trim($c)) => $id])
                ->merge(ServiceType::pluck('id', 'name')->mapWithKeys(fn($id, $n) => [trim($n) => $id]))
                ->toArray();

            $rawRows = $this->extractRawRows($dummyFile, $extension);
            $toInsert = [];

            foreach ($rawRows as $idx => $row) {
                $rowNum = $idx + 2;
                $date = $this->parseDate(trim($row['date'] ?? ''));
                $deptId = $departments[strtoupper(trim($row['department'] ?? ''))] ?? $departments[trim($row['department'] ?? '')] ?? null;
                $servId = $serviceTypes[strtoupper(trim($row['service_type'] ?? ''))] ?? $serviceTypes[trim($row['service_type'] ?? '')] ?? null;
                $hour = is_numeric($row['hour'] ?? null) ? (int)$row['hour'] : 8;
                $count = is_numeric($row['visit_count'] ?? null) ? (int)$row['visit_count'] : 0;

                if ($date && $deptId && $servId && $hour >= 0 && $hour <= 23 && $count >= 0) {
                    // Check if entry already exists in DB; if exists, update or upsert
                    $toInsert[] = [
                        'department_id' => $deptId,
                        'service_type_id' => $servId,
                        'service_date' => $date,
                        'service_hour' => $hour,
                        'visit_count' => $count,
                        'source_type' => $extension === 'csv' ? 'csv_import' : 'excel_import',
                        'batch_id' => $batch->id,
                        'source_row_number' => $rowNum,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'notes' => trim($row['notes'] ?? ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($toInsert)) {
                // Upsert to handle unique constraint gracefully
                DB::table('visit_statistics')->upsert(
                    $toInsert,
                    ['service_date', 'department_id', 'service_type_id', 'service_hour'],
                    ['visit_count', 'source_type', 'batch_id', 'source_row_number', 'updated_by', 'updated_at']
                );
                $insertedCount = count($toInsert);
            }

            // Update batch record status
            $batch->update([
                'status' => 'completed',
                'success_rows' => $insertedCount,
            ]);

            // Audit Log
            AuditLogService::log(
                action: 'import',
                module: 'imports',
                description: "นำเข้าข้อมูลจากไฟล์ '{$originalFilename}' สำเร็จ ({$insertedCount}/{$preview['total_rows']} แถว)",
                recordId: $batch->id,
                newValues: [
                    'batch_code' => $batchCode,
                    'file' => $originalFilename,
                    'success_rows' => $insertedCount,
                    'error_rows' => $preview['error_count'],
                ]
            );

            return $batch;
        });
    }

    /**
     * Rollback an entire import batch safely
     */
    public function rollbackBatch(ImportBatch $batch): bool
    {
        if (!$batch->canRollback()) {
            throw new Exception('ชุดข้อมูลนี้ไม่สามารถย้อนกลับได้ (อาจถูกยกเลิกไปแล้ว หรือยังไม่เสร็จสมบูรณ์)');
        }

        return DB::transaction(function () use ($batch) {
            $deletedCount = VisitStatistic::where('batch_id', $batch->id)->delete();

            $batch->update([
                'status' => 'rolled_back',
                'rolled_back_by' => Auth::id(),
                'rolled_back_at' => now(),
            ]);

            AuditLogService::log(
                action: 'rollback',
                module: 'imports',
                description: "ยกเลิกและย้อนกลับชุดนำเข้า {$batch->batch_code} (ลบสถิติ {$deletedCount} รายการ)",
                recordId: $batch->id
            );

            return true;
        });
    }

    /**
     * Extract raw rows from CSV or XLSX
     */
    protected function extractRawRows(UploadedFile $file, string $extension): array
    {
        $rows = [];

        if ($extension === 'csv' || $extension === 'txt') {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return [];
            }

            // Remove UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = null;
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                // If separator is semicolon, retry
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = str_getcsv($data[0], ';');
                }

                if (!$headers) {
                    $headers = array_map([$this, 'normalizeHeader'], $data);
                    continue;
                }

                if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                    continue; // Skip blank line
                }

                $row = [];
                foreach ($headers as $i => $h) {
                    $row[$h] = $data[$i] ?? '';
                }
                $rows[] = $row;
            }
            fclose($handle);
        } else {
            // If PhpSpreadsheet / Laravel Excel is available
            if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                try {
                    $array = \Maatwebsite\Excel\Facades\Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                        public function array(array $array) { return $array; }
                    }, $file);

                    if (!empty($array[0])) {
                        $rawExcel = $array[0];
                        $headerRow = array_shift($rawExcel);
                        $headers = array_map([$this, 'normalizeHeader'], $headerRow);

                        foreach ($rawExcel as $data) {
                            if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                                continue;
                            }
                            $row = [];
                            foreach ($headers as $i => $h) {
                                $row[$h] = (string)($data[$i] ?? '');
                            }
                            $rows[] = $row;
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback to basic extraction
                }
            }
        }

        return $rows;
    }

    /**
     * Normalize header names from Thai or English variations
     */
    protected function normalizeHeader(string $name): string
    {
        $clean = strtolower(trim($name));
        $clean = str_replace([' ', '_', '-', '*', '#'], '', $clean);

        if (str_contains($clean, 'date') || str_contains($clean, 'วัน') || str_contains($clean, 'บริการ')) {
            if (!str_contains($clean, 'บริการ') || str_contains($clean, 'วัน')) {
                return 'date';
            }
        }

        if (str_contains($clean, 'dept') || str_contains($clean, 'แผนก') || str_contains($clean, 'department')) {
            return 'department';
        }

        if (str_contains($clean, 'service') || str_contains($clean, 'ประเภท') || str_contains($clean, 'บริการ')) {
            return 'service_type';
        }

        if (str_contains($clean, 'hour') || str_contains($clean, 'ชั่วโมง') || str_contains($clean, 'เวลา') || str_contains($clean, 'time')) {
            return 'hour';
        }

        if (str_contains($clean, 'count') || str_contains($clean, 'จำนวน') || str_contains($clean, 'visit') || str_contains($clean, 'ครั้ง')) {
            return 'visit_count';
        }

        if (str_contains($clean, 'note') || str_contains($clean, 'หมายเหตุ') || str_contains($clean, 'remark')) {
            return 'notes';
        }

        return $clean;
    }

    /**
     * Parse date safely from multiple standard formats (YYYY-MM-DD, DD/MM/YYYY, etc.)
     */
    protected function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        // Try Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $raw)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // Try d/m/Y or d-m-Y
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw, $matches)) {
            try {
                $year = (int)$matches[3];
                // Support Thai Buddhist Era (2567 -> 2024)
                if ($year > 2400) {
                    $year -= 543;
                }
                return Carbon::create($year, (int)$matches[2], (int)$matches[1])->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        return null;
    }
}

