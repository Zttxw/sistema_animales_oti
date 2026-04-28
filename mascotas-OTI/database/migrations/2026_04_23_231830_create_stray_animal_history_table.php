<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 17 — create_stray_animal_history_table.php
// Historial de cambios de estado para animales callejeros.
// Usada por StrayAnimalController (store, updateStatus).
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('stray_animal_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stray_animal_id')
                  ->constrained('stray_animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string('new_status', 60);
            $table->text('description')->nullable();
            $table->foreignId('registered_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['stray_animal_id', 'created_at'], 'idx_stray_timeline');
        });
    }
    public function down(): void { Schema::dropIfExists('stray_animal_history'); }
};
