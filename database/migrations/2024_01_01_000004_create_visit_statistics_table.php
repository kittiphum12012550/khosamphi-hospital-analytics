<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visit_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();
            $table->date('service_date')->index()->comment('วันที่ให้บริการ');
            $table->unsignedTinyInteger('service_hour')->default(8)->comment('ชั่วโมงที่ให้บริการ (0-23)');
            $table->unsignedInteger('visit_count')->default(1)->comment('จำนวนครั้งการเข้ารับบริการ (Visits)');
            $table->enum('source_type', ['manual', 'excel_import', 'csv_import', 'api'])->default('manual');
            $table->foreignId('batch_id')->nullable()->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('source_row_number')->nullable()->comment('เลขแถวต้นฉบับในไฟล์');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for lightning-fast dashboard aggregation queries
            $table->index(['department_id', 'service_date']);
            $table->index(['service_type_id', 'service_date']);
            $table->index(['service_date', 'service_hour']);

            // Unique constraint to avoid double-counting duplicate entry for same slot
            $table->unique(
                ['service_date', 'department_id', 'service_type_id', 'service_hour'],
                'unique_visit_slot'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_statistics');
    }
};

