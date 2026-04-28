<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 06 — 2024_01_01_000006_create_vaccine_catalogs_table.php
// [FIX-14] Renamed from vaccine_catalog → vaccine_catalogs (Laravel plural)
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('vaccine_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('species_id')
                  ->nullable()
                  ->constrained('species')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vaccine_catalogs'); }
};
