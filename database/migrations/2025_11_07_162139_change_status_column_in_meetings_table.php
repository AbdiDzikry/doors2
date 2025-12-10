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
        Schema::table('meetings', function (Blueprint $table) {
            // Change the column to a string type, making it more flexible.
            $table->string('status', 255)->default('scheduled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Revert back to the original ENUM definition.
            // Note: This might cause data loss if new statuses were introduced.
            $table->enum('status', ['confirmed', 'pending', 'cancelled'])->default('pending')->change();
        });
    }
};