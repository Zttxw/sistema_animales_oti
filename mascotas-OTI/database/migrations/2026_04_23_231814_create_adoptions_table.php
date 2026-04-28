<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 15 — 2024_01_01_000015_create_adoptions_table.php
// [FIX-10] animal_id FK → restrictOnDelete()
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            // [FIX-10] RESTRICT: adoption record must be closed before animal is archived
            $table->foreignId('animal_id')
                  ->unique()
                  ->constrained('animals')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
            $table->text('reason')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('contact', 150)->nullable();
            $table->enum('status', ['AVAILABLE', 'IN_PROCESS', 'ADOPTED', 'WITHDRAWN'])
                  ->default('AVAILABLE');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('adopted_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->date('adopted_at')->nullable();
            $table->timestamps();
 
            $table->index('status', 'idx_status');
        });
    }
    public function down(): void { Schema::dropIfExists('adoptions'); }
};