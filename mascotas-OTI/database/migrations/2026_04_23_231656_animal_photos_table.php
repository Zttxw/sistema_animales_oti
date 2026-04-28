<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 10 — 2024_01_01_000010_create_animal_photos_table.php
// Column renamed: url → path (store relative path, resolve via Storage::url())
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('animal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                  ->constrained('animals')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            // Store relative path. Resolve full URL in model: Storage::url($this->path)
            $table->string('path', 500)->comment('Relative storage path');
            $table->boolean('is_cover')->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            $table->index('animal_id', 'idx_animal_id');
        });
    }
    public function down(): void { Schema::dropIfExists('animal_photos'); }
};
