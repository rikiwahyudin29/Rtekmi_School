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
        Schema::table('tbl_tugas_kumpul', function (Blueprint $table) {
            $table->foreign(['tugas_id'], 'tbl_tugas_kumpul_ibfk_1')->references(['id'])->on('tbl_tugas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['siswa_id'], 'tbl_tugas_kumpul_ibfk_2')->references(['id'])->on('tbl_siswa')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_tugas_kumpul', function (Blueprint $table) {
            $table->dropForeign('tbl_tugas_kumpul_ibfk_1');
            $table->dropForeign('tbl_tugas_kumpul_ibfk_2');
        });
    }
};
