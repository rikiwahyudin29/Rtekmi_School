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
        Schema::create('tbl_pkl', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->comment('Relasi ke tbl_siswa');
            $table->integer('dudi_id')->comment('Relasi ke tbl_dudi');
            $table->integer('guru_id')->comment('Relasi ke tbl_guru (Pembimbing)');
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->integer('nilai_dudi')->nullable()->default(0);
            $table->integer('nilai_sekolah')->nullable()->default(0);
            $table->string('file_laporan')->nullable();
            $table->enum('status_pkl', ['Pengajuan', 'Aktif', 'Selesai', 'Dibatalkan'])->nullable()->default('Pengajuan');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl');
    }
};
