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
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_type')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->foreignId('parent_meeting_id')->nullable()->constrained('meetings')->onDelete('cascade');
            $table->string('confirmation_status')->default('confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurring_type', 'recurring_end_date', 'parent_meeting_id', 'confirmation_status']);
        });
    }
};
