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
        Schema::create('tbl_presensi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->enum('role', ['siswa', 'guru']);
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
            $table->integer('jarak_meter')->nullable();
            $table->enum('status_kehadiran', ['Tepat Waktu', 'Terlambat', 'Izin', 'Sakit', 'Alpha'])->nullable()->default('Alpha');
            $table->enum('metode', ['QR', 'RFID', 'Manual', 'Online'])->nullable()->default('Online');
            $table->text('keterangan')->nullable();
            $table->enum('status_verifikasi', ['Pending', 'Disetujui', 'Ditolak'])->nullable()->default('Disetujui');
            $table->string('bukti_izin')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_presensi');
    }
};
