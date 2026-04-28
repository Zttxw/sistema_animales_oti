<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 02 — 2024_01_01_000002_create_breeds_table.php
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')
                  ->constrained('species')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string('name', 100);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['species_id', 'name'], 'uq_breed_species');
        });
    }
    public function down(): void { Schema::dropIfExists('breeds'); }
};
