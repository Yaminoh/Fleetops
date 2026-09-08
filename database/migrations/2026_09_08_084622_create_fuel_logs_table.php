<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fuel_logs')) {
            return;
        }

        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vehicle_id');
            $table->decimal('liters', 8, 2);
            $table->decimal('cost', 10, 2);
            $table->date('logged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
