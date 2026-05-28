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
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_lengkap', 100);
            $table->string('username', 50);
            $table->string('email', 100)->nullable();
            $table->string('nomor_wa', 20)->nullable();
            $table->string('telegram_chat_id', 50)->nullable();
            $table->string('password');
            $table->string('google2fa_secret')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->enum('role', ['admin', 'guru', 'siswa'])->default('admin');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->integer('active')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_users');
    }
};
