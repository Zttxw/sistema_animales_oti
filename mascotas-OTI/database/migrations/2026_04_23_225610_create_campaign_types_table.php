<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 03 — 2024_01_01_000003_create_campaign_types_table.php
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('description', 255)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('campaign_types'); }
};
 
