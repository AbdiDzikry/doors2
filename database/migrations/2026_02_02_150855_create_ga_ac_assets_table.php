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
        Schema::create('ga_ac_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For secure public QR access
            $table->string('sku')->unique()->comment('Code unit, e.g., GA-AC-001');
            $table->string('name')->comment('e.g., AC Ruang Server');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('pk')->nullable()->comment('Capacity, e.g., 2 PK');
            $table->string('location')->index();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->enum('status', ['good', 'needs_repair', 'broken', 'disposed'])->default('good');
            $table->text('notes')->nullable();
            $table->string('qr_path')->nullable(); // Path to generated QR image
            $table->timestamps();
            $table->softDeletes(); // Safety mechanism (recycle bin)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ga_ac_assets');
    }
};
