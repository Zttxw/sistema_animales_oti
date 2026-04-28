<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 07 — 2024_01_01_000007_create_settings_table.php
// [FIX-17] Runtime configuration — no redeploy needed to change values
// Seed examples:
//   recovery_token_ttl_hours = 24
//   max_animal_photos        = 10
//   notification_ttl_days    = 90
//   allowed_file_types       = jpg,jpeg,png,pdf
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }
    public function down(): void { Schema::dropIfExists('settings'); }
};
