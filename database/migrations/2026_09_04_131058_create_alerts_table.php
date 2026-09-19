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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trip_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icon', 50)->default('ℹ️');
            $table->string('title')->nullable();
            $table->text('detail')->nullable();
            $table->string('type')->nullable();
            $table->text('message')->nullable();
            $table->string('severity')->index()->default('info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
