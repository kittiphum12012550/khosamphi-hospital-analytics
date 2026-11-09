<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ImportBatch;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\VisitStatistic;
use App\Services\ImportValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_import_rejects_non_spreadsheet_file(): void
    {
        $staff = User::where('role', 'staff')->first();

        $file = UploadedFile::fake()->create('malicious.exe', 100);

        $response = $this->actingAs($staff)->post('/imports/preview', [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_preview_processes_valid_csv(): void
    {
        $staff = User::where('role', 'staff')->first();

        $csvContent = "service_date,department_code,service_type_code,service_hour,visit_count,notes\n";
        $csvContent .= "2024-11-05,OPD,GEN_EXAM,9,25,ช่วงเช้า\n";
        $csvContent .= "2024-11-05,ER,EMERGENCY,10,8,อุบัติเหตุ\n";

        $file = UploadedFile::fake()->createWithContent('test_visits.csv', $csvContent);

        $response = $this->actingAs($staff)->post('/imports/preview', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertSee('ผลการตรวจสอบข้อมูล');
        $response->assertSee('แถวที่ถูกต้อง');
    }

    public function test_rollback_batch_deletes_associated_visits(): void
    {
        $admin = User::where('role', 'admin')->first();
        $opd = Department::where('code', 'OPD')->first();
        $genExam = ServiceType::where('code', 'GEN_EXAM')->first();

        $batch = ImportBatch::create([
            'batch_code' => 'BATCH-TEST-ROLLBACK',
            'original_filename' => 'test.csv',
            'file_hash' => 'dummyhash123',
            'file_type' => 'csv',
            'total_rows' => 1,
            'success_rows' => 1,
            'status' => 'completed',
            'imported_by' => $admin->id,
        ]);

        $statistic = VisitStatistic::create([
            'department_id' => $opd->id,
            'service_type_id' => $genExam->id,
            'service_date' => '2025-01-01',
            'service_hour' => 10,
            'visit_count' => 15,
            'source_type' => 'csv_import',
            'batch_id' => $batch->id,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('visit_statistics', ['id' => $statistic->id]);

        $response = $this->actingAs($admin)->post("/imports/batches/{$batch->id}/rollback");

        $response->assertRedirect();
        $this->assertDatabaseMissing('visit_statistics', ['id' => $statistic->id]);

        $batch->refresh();
        $this->assertEquals('rolled_back', $batch->status);
    }
}

