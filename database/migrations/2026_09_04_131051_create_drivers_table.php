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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name')->nullable()->index();
            $table->string('employee_id')->unique()->index();
            $table->string('role')->default('Driver');
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('dispatch_count')->default(0);
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('status')->index()->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
