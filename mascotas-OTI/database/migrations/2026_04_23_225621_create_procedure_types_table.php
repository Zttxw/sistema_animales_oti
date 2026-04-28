<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 04 — 2024_01_01_000004_create_procedure_types_table.php
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('procedure_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('requires_detail')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('procedure_types'); }
};
