<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 20 — 2024_01_01_000020_create_campaign_actions_table.php
// [FIX-05] action_type (VARCHAR free-text) → procedure_type_id FK + action_detail
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                  ->constrained('campaigns')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('animal_id')
                  ->nullable()
                  ->constrained('animals')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            // [FIX-05] FK to procedure_types — prevents ungroupable free-text in reports.
            //          Reports can now GROUP BY procedure_type_id reliably.
            $table->foreignId('procedure_type_id')
                  ->constrained('procedure_types')
                  ->cascadeOnUpdate();
            $table->string('action_detail', 200)
                  ->nullable()
                  ->comment('Optional free-text — e.g. "Triple Feline" under Vaccination type');
            $table->text('description')->nullable();
            $table->foreignId('registered_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            $table->index('campaign_id',       'idx_campaign_id');
            $table->index('animal_id',         'idx_animal_id');
            $table->index('procedure_type_id', 'idx_procedure_type_id');
        });
    }
    public function down(): void { Schema::dropIfExists('campaign_actions'); }
};