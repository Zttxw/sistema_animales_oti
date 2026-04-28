<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// FILE 08 — 2024_01_01_000008_create_users_table.php
// [FIX-01] Added index on recovery_token
//
// NOTE: Run Spatie migrations AFTER this file:
//   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
//   php artisan migrate
// Initial roles to seed: ADMIN, VETERINARIAN, CITIZEN, INSPECTOR, COORDINATOR
// ============================================================
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('identity_document', 20)->unique();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'O'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->string('address', 255)->nullable();
            $table->string('sector', 100)->nullable()->comment('neighborhood / district / reference');
            $table->string('password');
            $table->enum('status', ['ACTIVE', 'SUSPENDED', 'INACTIVE'])->default('ACTIVE');
            $table->rememberToken();
            $table->string('recovery_token', 255)->nullable();
            $table->timestamp('recovery_token_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
 
            $table->index('identity_document', 'idx_identity_document');
            $table->index('email',             'idx_email');
            $table->index('status',            'idx_status');
            // [FIX-01] Password reset link lookup requires this index
            $table->index('recovery_token',    'idx_recovery_token');
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};