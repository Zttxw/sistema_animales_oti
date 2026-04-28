<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 17 — 2024_01_01_000017_create_stray_animal_photos_table.php
// Column renamed: url → path
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('stray_animal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stray_animal_id')
                  ->constrained('stray_animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string('path', 500)->comment('Relative storage path');
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            $table->index('stray_animal_id', 'idx_stray_animal_id');
        });
    }
    public function down(): void { Schema::dropIfExists('stray_animal_photos'); }
};
