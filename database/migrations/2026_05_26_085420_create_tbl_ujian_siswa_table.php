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
        Schema::create('tbl_ujian_siswa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_jadwal')->nullable()->index('fk_ujian_jadwal_aman');
            $table->integer('id_bank_soal');
            $table->integer('id_siswa');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai_seharusnya')->nullable();
            $table->dateTime('waktu_submit')->nullable();
            $table->integer('status')->nullable()->default(0)->comment('0=Mengerjakan, 1=Selesai');
            $table->decimal('nilai', 10)->nullable()->default(0);
            $table->integer('jml_benar')->nullable()->default(0);
            $table->integer('jml_salah')->nullable()->default(0);
            $table->integer('jml_kosong')->nullable()->default(0);
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('lat_siswa', 50)->nullable();
            $table->string('long_siswa', 50)->nullable();
            $table->float('jarak_meter')->nullable();
            $table->integer('is_blocked')->nullable()->default(0)->comment('1=Kena Banned');
            $table->text('alasan_blokir')->nullable();
            $table->boolean('is_locked')->nullable()->default(false)->comment('1=Terkunci (Timeout), 0=Aman');
            $table->integer('jml_pelanggaran')->nullable()->default(0)->comment('Counter pelanggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ujian_siswa');
    }
};
