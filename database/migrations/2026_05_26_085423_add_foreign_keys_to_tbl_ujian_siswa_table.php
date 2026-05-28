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
        Schema::table('tbl_ujian_siswa', function (Blueprint $table) {
            $table->foreign(['id_jadwal'], 'fk_ujian_jadwal')->references(['id'])->on('tbl_jadwal_ujian')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_jadwal'], 'fk_ujian_jadwal_aman')->references(['id'])->on('tbl_jadwal_ujian')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_jadwal'], 'fk_ujian_jadwal_baru')->references(['id'])->on('tbl_jadwal_ujian')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_ujian_siswa', function (Blueprint $table) {
            $table->dropForeign('fk_ujian_jadwal');
            $table->dropForeign('fk_ujian_jadwal_aman');
            $table->dropForeign('fk_ujian_jadwal_baru');
        });
    }
};
