<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 25 — 2024_01_01_000025_create_notifications_table.php
// [FIX-16] Added expires_at for TTL pruning
//          Rename: read → is_read (avoids conflict with PHP reserved word)
//
// Prune via MassPrunable in the Notification model:
//   public function prunable(): Builder {
//       return static::where('expires_at', '<', now());
//   }
// Schedule: $schedule->command('model:prune')->daily();
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->enum('type', ['CAMPAIGN', 'PARTICIPATION', 'NOTICE', 'ADMIN', 'SYSTEM']);
            $table->string('title', 200)->nullable();
            $table->text('message');
            // [FIX-16] Renamed read → is_read (read is a reserved word in some contexts)
            $table->boolean('is_read')->default(false);
            $table->string('notifiable_type', 60)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            // [FIX-16] Set by app from settings.notification_ttl_days on insert
            $table->timestamp('expires_at')
                  ->nullable()
                  ->comment('Set by app from settings[notification_ttl_days]');
            $table->timestamp('created_at')->nullable()->useCurrent();
 
            // Compound: unread badge count (most frequent query in the app)
            $table->index(['user_id', 'is_read'],           'idx_user_read');
            $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifiable');
            // [FIX-16] Pruning job uses this index
            $table->index('expires_at', 'idx_expires_at');
        });
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};
