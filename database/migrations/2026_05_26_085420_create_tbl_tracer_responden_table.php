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
        Schema::create('tbl_tracer_responden', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->unique('siswa_id');
            $table->enum('status_kegiatan', ['Bekerja', 'Kuliah', 'Wirausaha', 'Mencari Kerja', 'Lainnya']);
            $table->string('nama_instansi')->nullable()->comment('Nama Kampus atau Tempat Kerja');
            $table->dateTime('tanggal_isi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tracer_responden');
    }
};
