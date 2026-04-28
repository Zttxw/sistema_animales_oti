<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 27 — create_audit_logs_table.php
// Tabla de auditoría para login/logout y acciones críticas.
// Usada por AuthController (login, logout).
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->string('action', 60)->comment('LOGIN|LOGOUT|CREATE|UPDATE|DELETE');
            $table->string('table_name', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('user_id', 'idx_audit_user_id');
            $table->index('action', 'idx_audit_action');
            $table->index('created_at', 'idx_audit_created_at');
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
