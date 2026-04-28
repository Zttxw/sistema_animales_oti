<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 11 — 2024_01_01_000011_create_animal_history_table.php
// [FIX-12] Compound index (animal_id, created_at DESC) for timeline queries
// [FIX-13] Renamed user_id → registered_by for cross-table naming consistency
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('animal_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                  ->constrained('animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            // Reverted back to user_id to match SQL dump, models and controllers
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->string('change_type', 60)->comment('STATUS|VACCINE|PROCEDURE|ADOPTION|DATA');
            $table->text('description')->nullable();
            $table->json('previous_data')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            // [FIX-12] Timeline query: last N events for a given animal
            $table->index(['animal_id', 'created_at'], 'idx_animal_timeline');
            $table->index('change_type', 'idx_change_type');
        });
    }
    public function down(): void { Schema::dropIfExists('animal_history'); }
};
