<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('fuel_logs', 'receipt_image')) {
            Schema::table('fuel_logs', function (Blueprint $table): void {
                $table->string('receipt_image')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Keep this nullable compatibility column: earlier fuel-log schemas may already have it.
    }
};
