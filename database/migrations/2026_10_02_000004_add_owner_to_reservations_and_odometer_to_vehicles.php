<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations') && ! Schema::hasColumn('reservations', 'user_id')) {
            Schema::table('reservations', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->after('employee_id')->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('vehicles') && ! Schema::hasColumn('vehicles', 'odometer')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->unsignedBigInteger('odometer')->nullable()->after('fuel_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'user_id')) {
            Schema::table('reservations', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'odometer')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->dropColumn('odometer');
            });
        }
    }
};
