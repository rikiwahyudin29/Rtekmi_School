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
        Schema::create('tbl_bank_soal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_bank', 50);
            $table->string('judul_ujian');
            $table->integer('id_mapel');
            $table->integer('id_guru');
            $table->integer('jumlah_soal')->nullable()->default(0);
            $table->string('status', 20)->nullable()->default('Tidak Aktif');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('durasi')->nullable()->default(60);
            $table->integer('acak_soal')->nullable()->default(1);
            $table->integer('wajib_lokasi')->nullable()->default(0)->comment('1=Wajib GPS');
            $table->string('lat_ujian', 50)->nullable();
            $table->string('long_ujian', 50)->nullable();
            $table->integer('radius_ujian')->nullable()->default(100);
            $table->string('token', 6)->nullable()->comment('Token Masuk Ujian');
            $table->integer('acak_opsi')->nullable()->default(0)->comment('1=Jawaban Diacak, 0=Urut Abjad');
            $table->integer('jumlah_soal_pg')->nullable()->default(0)->comment('Total Soal Pilihan Ganda');
            $table->integer('jumlah_soal_esai')->nullable()->default(0)->comment('Total Soal Esai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bank_soal');
    }
};
