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
        Schema::table('tbl_tugas', function (Blueprint $table) {
            $table->foreign(['guru_id'], 'tbl_tugas_ibfk_1')->references(['id'])->on('tbl_guru')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mapel_id'], 'tbl_tugas_ibfk_2')->references(['id'])->on('tbl_mapel')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['kelas_id'], 'tbl_tugas_ibfk_3')->references(['id'])->on('tbl_kelas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_tugas', function (Blueprint $table) {
            $table->dropForeign('tbl_tugas_ibfk_1');
            $table->dropForeign('tbl_tugas_ibfk_2');
            $table->dropForeign('tbl_tugas_ibfk_3');
        });
    }
};
