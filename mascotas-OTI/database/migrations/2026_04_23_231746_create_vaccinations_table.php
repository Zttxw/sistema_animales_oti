<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 13 — 2024_01_01_000013_create_vaccinations_table.php
// [FIX-06] vaccine_name synced from catalog in Vaccination::saving()
//          Column renamed: file_url → file_path (relative path convention)
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                  ->constrained('animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            // [FIX-06] When vaccine_id is set, app syncs vaccine_name from catalog
            $table->foreignId('vaccine_id')
                  ->nullable()
                  ->constrained('vaccine_catalogs')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->string('vaccine_name', 100)
                  ->comment('Auto-synced from catalog when vaccine_id is set; free-text otherwise');
            $table->date('applied_at');
            $table->date('next_dose_at')->nullable();
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
            $table->timestamps();
 
            $table->index('animal_id',     'idx_animal_id');
            $table->index('next_dose_at',  'idx_next_dose_at');
        });
    }
    public function down(): void { Schema::dropIfExists('vaccinations'); }
};
