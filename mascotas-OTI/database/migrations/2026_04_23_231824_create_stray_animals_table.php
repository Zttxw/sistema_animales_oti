<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FILE 16 — 2024_01_01_000016_create_stray_animals_table.php
// No changes from v4 — design was correct.
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('stray_animals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('SJ-C-YYYY-NNNNNN');
            $table->foreignId('species_id')
                  ->nullable()
                  ->constrained('species')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('breed_id')
                  ->nullable()
                  ->constrained('breeds')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->enum('approx_gender', ['M', 'F', 'UNKNOWN'])->default('UNKNOWN');
            $table->string('color', 100)->nullable();
            $table->enum('size', ['SMALL', 'MEDIUM', 'LARGE', 'GIANT'])->nullable();
            $table->text('location');
            $table->enum('status', ['OBSERVED', 'RESCUED', 'IN_TREATMENT', 'FOR_ADOPTION', 'DECEASED', 'RELEASED'])
                  ->default('OBSERVED');
            $table->text('notes')->nullable();
            $table->foreignId('reported_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
 
            $table->index('status',     'idx_status');
            $table->index('species_id', 'idx_species_id');
        });
    }
    public function down(): void { Schema::dropIfExists('stray_animals'); }
};