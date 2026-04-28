<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 22 — 2024_01_01_000022_create_lost_notices_table.php
// [FIX-09] animal_id FK → restrictOnDelete()
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('lost_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                  ->unique()
                  ->constrained('posts')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            // [FIX-09] RESTRICT: notice must be closed before animal record is removed
            $table->foreignId('animal_id')
                  ->constrained('animals')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
            $table->date('lost_at');
            $table->text('lost_location');
            $table->text('description')->nullable();
            $table->string('contact', 150)->nullable();
            $table->enum('status', ['ACTIVE', 'FOUND', 'CLOSED'])->default('ACTIVE');
            $table->timestamps();
 
            $table->index('animal_id', 'idx_animal_id');
        });
    }
    public function down(): void { Schema::dropIfExists('lost_notices'); }
};
