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
        Schema::create('tbl_kompetensi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tahun_akademik_id');
            $table->integer('id_kelas');
            $table->integer('id_mapel');
            $table->integer('guru_id');
            $table->enum('kurikulum', ['Kurmer', 'K13'])->default('Kurmer');
            $table->string('kode', 20)->comment('Cth: TP.1, 3.1 (Pengetahuan), 4.1 (Keterampilan)');
            $table->text('deskripsi')->comment('Isi Tujuan Pembelajaran / Kompetensi Dasar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kompetensi');
    }
};
