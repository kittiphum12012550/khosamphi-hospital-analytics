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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 60)->unique()->comment('รหัสชุดการนำเข้า เช่น BATCH-20241109-001');
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->string('file_hash', 64)->index()->comment('SHA-256 ป้องกันไฟล์ซ้ำ');
            $table->string('file_type', 10)->default('csv');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('error_rows')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'rolled_back'])->default('pending')->index();
            $table->json('error_summary')->nullable()->comment('สรุปข้อผิดพลาดรายแถว');
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rolled_back_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rolled_back_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};

