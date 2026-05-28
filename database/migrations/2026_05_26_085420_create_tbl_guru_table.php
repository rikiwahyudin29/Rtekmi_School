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
        Schema::create('tbl_guru', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_user')->nullable();
            $table->string('dapodik_id', 50)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('nip', 30)->nullable();
            $table->string('nik', 20)->nullable();
            $table->enum('jk', ['L', 'P'])->nullable()->default('L');
            $table->string('rfid_uid', 50)->nullable();
            $table->string('qr_code', 100)->nullable();
            $table->string('nama_lengkap', 100)->nullable();
            $table->string('nuptk', 30)->nullable();
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->default('L');
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('sertifikasi', 100)->nullable();
            $table->enum('status_guru', ['PNS', 'GTT', 'GTY', 'HONORER'])->nullable()->default('GTY');
            $table->string('foto')->nullable()->default('default.png');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('ibu_kandung', 100)->nullable();
            $table->string('status_kepegawaian', 50)->nullable();
            $table->string('password')->nullable();
            $table->string('role', 20)->nullable()->default('guru');
            $table->text('google_refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_guru');
    }
};
