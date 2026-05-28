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
        Schema::create('tbl_jawaban_siswa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_ujian_siswa');
            $table->integer('id_soal');
            $table->integer('id_opsi')->nullable();
            $table->text('jawaban_siswa')->nullable();
            $table->text('jawaban_isian')->nullable();
            $table->integer('ragu')->nullable()->default(0);
            $table->integer('is_benar')->nullable()->default(0);
            $table->float('nilai')->nullable()->default(0);
            $table->float('nilai_esai')->nullable()->default(0)->comment('Nilai per butir soal');
            $table->integer('nomor_urut')->nullable()->default(0)->comment('Urutan Nomor Soal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jawaban_siswa');
    }
};
