<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add the reservation-workflow fields without removing existing reservation data. */
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservations', 'reservation_no')) $table->string('reservation_no')->nullable()->unique();
            if (! Schema::hasColumn('reservations', 'employee_id')) $table->string('employee_id')->nullable();
            if (! Schema::hasColumn('reservations', 'destination')) $table->string('destination')->nullable();
            if (! Schema::hasColumn('reservations', 'purpose')) $table->string('purpose')->nullable();
            if (! Schema::hasColumn('reservations', 'requested_date')) $table->date('requested_date')->nullable();
            if (! Schema::hasColumn('reservations', 'requested_time')) $table->time('requested_time')->nullable();
            if (! Schema::hasColumn('reservations', 'passenger_count')) $table->unsignedInteger('passenger_count')->nullable();
            if (! Schema::hasColumn('reservations', 'remarks')) $table->text('remarks')->nullable();
            if (! Schema::hasColumn('reservations', 'approved_by')) $table->unsignedBigInteger('approved_by')->nullable();
            if (! Schema::hasColumn('reservations', 'approved_at')) $table->timestamp('approved_at')->nullable();
            if (! Schema::hasColumn('reservations', 'updated_at')) $table->timestamp('updated_at')->nullable();
        });

        if (Schema::hasColumn('reservations', 'reservation_date')) {
            DB::table('reservations')->whereNull('requested_date')->update([
                'requested_date' => DB::raw('reservation_date'),
            ]);
        }

        if (Schema::hasColumn('reservations', 'driver_name')) {
            DB::table('reservations')->whereNull('employee_id')->update([
                'employee_id' => DB::raw("'LEGACY-' || id"),
            ]);
        }

        DB::table('reservations')->whereNull('reservation_no')->update([
            'reservation_no' => DB::raw("'RES-LEGACY-' || id"),
        ]);
    }

    public function down(): void
    {
        // Keep compatibility fields because they may contain operational records.
    }
};
