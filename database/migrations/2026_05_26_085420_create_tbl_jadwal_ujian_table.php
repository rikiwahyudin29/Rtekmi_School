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
        Schema::create('tbl_jadwal_ujian', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_ujian');
            $table->string('kode_ujian', 50)->nullable();
            $table->integer('id_bank_soal');
            $table->integer('id_guru');
            $table->integer('id_ruangan')->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('durasi')->nullable()->default(60)->comment('Menit');
            $table->string('token', 10)->nullable();
            $table->integer('acak_soal')->nullable()->default(1);
            $table->integer('acak_opsi')->nullable()->default(0);
            $table->integer('wajib_lokasi')->nullable()->default(0);
            $table->integer('status')->nullable()->default(1)->comment('1=Aktif, 0=Nonaktif');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->integer('limit_pelanggaran')->nullable()->default(3)->comment('Batas Max Pelanggaran');
            $table->integer('min_waktu')->nullable()->default(10)->comment('Menit minimal sebelum tombol selesai muncul');
            $table->integer('bobot_pg')->nullable()->default(100);
            $table->integer('bobot_esai')->nullable()->default(0);
            $table->integer('min_waktu_selesai')->nullable()->default(0)->comment('Menit minimal sebelum tombol selesai muncul');
            $table->boolean('setting_strict')->nullable()->default(false)->comment('1=Aktif, 0=Nonaktif');
            $table->integer('setting_afk_timeout')->nullable()->default(0)->comment('Detik toleransi keluar tab');
            $table->integer('setting_max_violation')->nullable()->default(3)->comment('Batas pelanggaran');
            $table->boolean('setting_show_score')->nullable()->default(false)->comment('Tampilkan nilai setelah selesai');
            $table->boolean('setting_multi_login')->nullable()->default(false)->comment('1=Cegah login ganda');
            $table->boolean('setting_token')->nullable()->default(false);
            $table->enum('status_ujian', ['AKTIF', 'NONAKTIF', 'SELESAI'])->nullable()->default('AKTIF');
            $table->integer('id_tahun_ajaran')->nullable();
            $table->integer('id_jenis_ujian')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jadwal_ujian');
    }
};
