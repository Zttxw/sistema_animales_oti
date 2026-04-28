<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 24 — 2024_01_01_000024_create_comments_table.php
// [FIX-08] user_id → nullable + nullOnDelete() (preserve moderation evidence)
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                  ->constrained('posts')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            // [FIX-08] Nullable: user can be deactivated/deleted; comment stays for moderation audit.
            //          A deleted user's comments appear as "anonymous" in the UI.
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->text('content');
            $table->enum('status', ['VISIBLE', 'HIDDEN', 'DELETED'])->default('VISIBLE');
            $table->foreignId('moderated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->string('moderation_reason', 255)->nullable();
            $table->timestamps();
 
            $table->index('post_id', 'idx_post_id');
            $table->index('status',  'idx_status');
        });
    }
    public function down(): void { Schema::dropIfExists('comments'); }
};
