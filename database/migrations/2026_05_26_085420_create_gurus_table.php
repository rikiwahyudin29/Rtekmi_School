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
        Schema::create('gurus', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index('gurus_user_id_foreign');
            $table->string('nip', 20)->nullable();
            $table->string('nama_lengkap', 100);
            $table->string('gelar_depan', 20)->nullable();
            $table->string('gelar_belakang', 20)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->text('alamat')->nullable();
            $table->string('foto')->default('default.jpg');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
