<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'two_factor_code')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('two_factor_code')->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'two_factor_expires_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('two_factor_expires_at')->nullable();
            });
        }

        if (! Schema::hasTable('trusted_devices')) {
            Schema::create('trusted_devices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('selector', 24)->unique();
                $table->string('token_hash', 64);
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');

        if (Schema::hasColumn('users', 'two_factor_expires_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('two_factor_expires_at'));
        }

        if (Schema::hasColumn('users', 'two_factor_code')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('two_factor_code'));
        }
    }
};