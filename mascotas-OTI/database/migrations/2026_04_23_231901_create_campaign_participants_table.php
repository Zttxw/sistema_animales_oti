<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 19 — 2024_01_01_000019_create_campaign_participants_table.php
// [FIX-04] NULL-safe UNIQUE via generated column animal_id_safe
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                  ->constrained('campaigns')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreignId('animal_id')
                  ->nullable()
                  ->constrained('animals')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->boolean('attended')->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            $table->unique(['campaign_id', 'user_id', 'animal_id'], 'uq_camp_user_animal');
            $table->index('campaign_id', 'idx_campaign_id');
            $table->index('user_id',     'idx_user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('campaign_participants'); }
};