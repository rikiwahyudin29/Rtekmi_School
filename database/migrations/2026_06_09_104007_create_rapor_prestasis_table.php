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
        Schema::create('tbl_rapor_prestasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('tbl_siswa')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tbl_tahun_ajaran')->onDelete('cascade');
            $table->integer('semester');
            $table->string('jenis_prestasi');
            $table->string('nama_prestasi');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_rapor_prestasi');
    }
};
