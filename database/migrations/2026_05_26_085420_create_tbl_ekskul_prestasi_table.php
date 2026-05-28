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
        Schema::create('tbl_ekskul_prestasi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ekskul_id');
            $table->string('nama_lomba');
            $table->enum('tingkat', ['Sekolah', 'Kab/Kota', 'Provinsi', 'Nasional', 'Internasional']);
            $table->string('juara', 50);
            $table->date('tanggal');
            $table->string('foto_dokumentasi');
            $table->text('deskripsi_caption')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_prestasi');
    }
};
