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
        Schema::create('tbl_surat_keluar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('template_id')->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->enum('jenis_surat', ['Keterangan Aktif', 'Kelakuan Baik', 'Panggilan Ortu', 'Mutasi', 'Lainnya'])->nullable();
            $table->integer('siswa_id')->nullable();
            $table->string('tujuan')->nullable();
            $table->string('perihal')->nullable();
            $table->longText('isi_final')->nullable();
            $table->text('isi_ringkas')->nullable();
            $table->date('tgl_surat')->nullable();
            $table->integer('ttd_oleh')->nullable();
            $table->string('token_validasi', 100)->nullable();
            $table->enum('status', ['Draft', 'Disetujui', 'Ditolak'])->nullable()->default('Draft');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_surat_keluar');
    }
};
