<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 09 — 2024_01_01_000009_create_animals_table.php
// [FIX-02] user_id FK → restrictOnDelete() (explicit RESTRICT)
// NOTE: municipal_code must be generated inside a DB::transaction()
//       with ->lockForUpdate() to prevent race conditions.
//       See app/Models/Animal.php → protected static function booted()
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('municipal_code', 20)
                  ->unique()
                  ->comment('SJ-YYYY-NNNNNN — generated in Animal::creating() with lockForUpdate()');
            // [FIX-02] restrictOnDelete: cannot delete user who owns animals
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('species_id')
                  ->constrained('species')
                  ->cascadeOnUpdate();
            $table->foreignId('breed_id')
                  ->nullable()
                  ->constrained('breeds')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->string('name', 100);
            $table->enum('gender', ['M', 'F', 'UNKNOWN'])->default('UNKNOWN');
            $table->date('birth_date')->nullable();
            // approximate_age used ONLY when birth_date IS NULL
            $table->string('approximate_age', 30)->nullable();
            $table->string('color', 100)->nullable();
            $table->enum('size', ['SMALL', 'MEDIUM', 'LARGE', 'GIANT'])->nullable();
            $table->enum('reproductive_status', ['INTACT', 'SPAYED', 'NEUTERED', 'UNKNOWN'])->nullable();
            $table->text('distinctive_features')->nullable();
            $table->enum('status', ['ACTIVE', 'LOST', 'FOR_ADOPTION', 'DECEASED'])->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->date('death_date')->nullable();
            $table->text('death_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
 
            $table->index('user_id',        'idx_user_id');
            $table->index('species_id',     'idx_species_id');
            $table->index('status',         'idx_status');
            $table->index('municipal_code', 'idx_municipal_code');
            // Compound: owner dashboard (user + status filter)
            $table->index(['user_id', 'status'], 'idx_user_status');
        });
    }
    public function down(): void { Schema::dropIfExists('animals'); }
};