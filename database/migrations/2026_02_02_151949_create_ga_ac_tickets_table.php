<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ga_ac_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public reference

            // Relasi ke Asset
            $table->foreignId('ga_ac_asset_id')->constrained('ga_ac_assets')->onDelete('cascade');

            // Reporter Info (Public)
            $table->string('reporter_name');
            $table->string('reporter_nik');

            // Issue Details
            $table->enum('issue_category', [
                'not_cold',
                'leaking',
                'noisy',
                'dead',
                'smell',
                'other'
            ]);
            $table->text('description')->nullable();

            // Workflow Status
            // pending_validation -> open -> assigned -> in_progress -> resolved -> closed
            // false_alarm (rejected)
            $table->enum('status', [
                'pending_validation',
                'false_alarm',
                'open',
                'assigned',
                'in_progress',
                'resolved',
                'closed'
            ])->default('pending_validation');

            // GA Validation
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->dateTime('validated_at')->nullable();

            // Technician Assignment
            $table->foreignId('technician_id')->nullable()->constrained('users');

            // Resolution
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('repair_cost', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ga_ac_tickets');
    }
};
