<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 23 — 2024_01_01_000023_create_post_photos_table.php
// Column renamed: url → path
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('post_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                  ->constrained('posts')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string('path', 500)->comment('Relative storage path');
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            $table->index('post_id', 'idx_post_id');
        });
    }
    public function down(): void { Schema::dropIfExists('post_photos'); }
};
