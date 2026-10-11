<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('fuel_logs', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('fuel_logs', 'fuel_level_before')) {
                $table->decimal('fuel_level_before', 5, 2)->nullable();
            }

            if (! Schema::hasColumn('fuel_logs', 'fuel_level_after')) {
                $table->decimal('fuel_level_after', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('fuel_logs', 'driver_id')) {
                $table->dropConstrainedForeignId('driver_id');
            }

            foreach (['fuel_level_before', 'fuel_level_after'] as $column) {
                if (Schema::hasColumn('fuel_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
