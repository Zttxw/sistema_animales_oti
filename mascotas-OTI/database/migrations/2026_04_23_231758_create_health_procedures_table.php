<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 14 — 2024_01_01_000014_create_health_procedures_table.php
// [FIX-07] Added updated_at (procedures can be corrected by admin)
//          Column renamed: file_url → file_path
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('health_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                  ->constrained('animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('procedure_type_id')
                  ->constrained('procedure_types')
                  ->cascadeOnUpdate();
            $table->string('type_detail', 100)->nullable();
            $table->date('performed_at');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path', 500)->nullable()->comment('Relative storage path');
            $table->foreignId('registered_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('campaign_id')
                  ->nullable()
                  ->constrained('campaigns')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            // [FIX-07] Was missing — admin corrections need an edit timestamp
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
 
            $table->index('animal_id',          'idx_animal_id');
            $table->index('procedure_type_id',  'idx_procedure_type_id');
        });
    }
    public function down(): void { Schema::dropIfExists('health_procedures'); }
};
