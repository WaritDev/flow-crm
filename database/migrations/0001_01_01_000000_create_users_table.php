<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // UNSIGNED BIGINT AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // varchar(255)
            $table->string('email')->unique(); // UNIQUE
            $table->timestamp('email_verified_at')->nullable(); //
            $table->string('password');
            $table->rememberToken();
            $table->timestamps(); // created_at, updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable(); //created_at
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ไม่มี AUTO_INCREMENT
            $table->foreignId('user_id')->nullable()->index(); // index ไว้สำหรับ search และ filter ใน DB ได้
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable(); // ใช้ browser ไหน
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
