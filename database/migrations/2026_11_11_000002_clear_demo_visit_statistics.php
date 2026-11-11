<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('visit_statistics')->delete();
    }

    public function down(): void
    {
        // Deleted demo records cannot be restored.
    }
};
