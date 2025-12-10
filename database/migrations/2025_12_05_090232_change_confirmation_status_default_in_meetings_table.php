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
            // Change the default value of confirmation_status to 'pending_confirmation'
            $table->string('confirmation_status')->default('pending_confirmation')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Revert the default value back to 'confirmed'
            $table->string('confirmation_status')->default('confirmed')->change();
        });
    }
};