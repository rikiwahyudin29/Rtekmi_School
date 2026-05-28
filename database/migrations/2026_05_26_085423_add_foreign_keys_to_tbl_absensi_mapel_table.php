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
        Schema::table('tbl_absensi_mapel', function (Blueprint $table) {
            $table->foreign(['id_jurnal'], 'tbl_absensi_mapel_ibfk_1')->references(['id'])->on('tbl_jurnal')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_siswa'], 'tbl_absensi_mapel_ibfk_2')->references(['id'])->on('tbl_siswa')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_absensi_mapel', function (Blueprint $table) {
            $table->dropForeign('tbl_absensi_mapel_ibfk_1');
            $table->dropForeign('tbl_absensi_mapel_ibfk_2');
        });
    }
};
