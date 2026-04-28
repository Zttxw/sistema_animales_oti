<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 21 — 2024_01_01_000021_create_posts_table.php
// [FIX-15] Added compound index for published listing queries
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_type_id')
                  ->constrained('post_types')
                  ->cascadeOnUpdate();
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->foreignId('author_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'DISABLED', 'FEATURED'])->default('DRAFT');
            $table->softDeletes();
            $table->timestamps();
 
            $table->index('post_type_id', 'idx_post_type_id');
            $table->index('author_id',    'idx_author_id');
            // [FIX-15] Most frequent query: WHERE status = 'PUBLISHED' AND deleted_at IS NULL ORDER BY created_at DESC
            $table->index(['status', 'deleted_at', 'created_at'], 'idx_posts_published');
        });
    }
    public function down(): void { Schema::dropIfExists('posts'); }
};