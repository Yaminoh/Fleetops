<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('fuel_logs', 'fuel_level_before_image')) {
                $table->string('fuel_level_before_image')->nullable();
            }

            if (! Schema::hasColumn('fuel_logs', 'fuel_level_after_image')) {
                $table->string('fuel_level_after_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['fuel_level_before_image', 'fuel_level_after_image'],
                fn (string $column): bool => Schema::hasColumn('fuel_logs', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
