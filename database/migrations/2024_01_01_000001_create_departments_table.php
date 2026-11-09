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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('รหัสแผนก เช่น OPD, ER');
            $table->string('name_th', 150)->comment('ชื่อภาษาไทย');
            $table->string('name_en', 150)->nullable()->comment('ชื่อภาษาอังกฤษ');
            $table->text('description')->nullable();
            $table->string('phone', 50)->nullable()->comment('เบอร์โทรศัพท์ภายใน');
            $table->string('location', 100)->nullable()->comment('สถานที่ตั้งใน รพ.');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

