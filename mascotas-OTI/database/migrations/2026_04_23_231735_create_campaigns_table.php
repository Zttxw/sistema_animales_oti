<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 12 — 2024_01_01_000012_create_campaigns_table.php
// [FIX-03] Merged date + time → scheduled_at DATETIME
// MUST be created before vaccinations and health_procedures (FK dependency)
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->foreignId('campaign_type_id')
                  ->constrained('campaign_types')
                  ->cascadeOnUpdate();
            $table->text('description')->nullable();
            // [FIX-03] Single DATETIME — enables clean temporal queries
            $table->dateTime('scheduled_at')
                  ->comment('Campaign start — replaces separate date + time columns');
            $table->string('location', 200)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'IN_PROGRESS', 'FINISHED', 'CANCELLED'])
                  ->default('DRAFT');
            $table->text('target_audience')->nullable();
            $table->text('requirements')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamps();
 
            $table->index('status',              'idx_status');
            $table->index('campaign_type_id',    'idx_campaign_type_id');
            // Compound: upcoming campaigns list (status + date window)
            $table->index(['status', 'scheduled_at'], 'idx_status_scheduled');
        });
    }
    public function down(): void { Schema::dropIfExists('campaigns'); }
};
