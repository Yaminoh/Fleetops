<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fuel_logs') && ! Schema::hasColumn('fuel_logs', 'user_id')) {
            Schema::table('fuel_logs', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fuel_logs') && Schema::hasColumn('fuel_logs', 'user_id')) {
            Schema::table('fuel_logs', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
