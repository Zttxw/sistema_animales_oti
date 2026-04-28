<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 01 — 2024_01_01_000001_create_species_table.php
// ============================================================
 
return new class extends Migration {
    public function up(): void {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('species'); }
};
