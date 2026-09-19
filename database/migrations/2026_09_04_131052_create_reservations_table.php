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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_no')->unique()->index();
            $table->string('employee_id')->index();
            $table->string('destination')->nullable();
            $table->string('purpose')->nullable();
            $table->date('requested_date')->index();
            $table->time('requested_time')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('passenger_count')->default(1);
            $table->text('remarks')->nullable();
            $table->string('status')->index()->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
