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
        Schema::create('tbl_jurnal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_guru');
            $table->integer('id_tahun_ajaran')->nullable();
            $table->integer('id_kelas');
            $table->integer('id_mapel');
            $table->date('tanggal');
            $table->string('jam_ke', 50);
            $table->text('materi');
            $table->text('keterangan')->nullable();
            $table->string('foto_kegiatan')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jurnal');
    }
};
