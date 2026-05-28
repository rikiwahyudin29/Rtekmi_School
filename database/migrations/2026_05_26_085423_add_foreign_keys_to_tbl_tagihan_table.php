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
        Schema::table('tbl_tagihan', function (Blueprint $table) {
            $table->foreign(['id_jenis_bayar'], 'tbl_tagihan_ibfk_1')->references(['id'])->on('tbl_jenis_bayar')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_siswa'], 'tbl_tagihan_ibfk_2')->references(['id'])->on('tbl_siswa')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_tagihan', function (Blueprint $table) {
            $table->dropForeign('tbl_tagihan_ibfk_1');
            $table->dropForeign('tbl_tagihan_ibfk_2');
        });
    }
};
