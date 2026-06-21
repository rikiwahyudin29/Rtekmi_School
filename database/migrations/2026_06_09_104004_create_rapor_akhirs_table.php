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
        if (!Schema::hasTable('tbl_rapor_akhir')) {
            Schema::create('tbl_rapor_akhir', function (Blueprint $table) {
                $table->id();
                $table->integer('siswa_id');
                $table->integer('mapel_id');
                $table->integer('guru_id');
                $table->integer('tahun_ajaran_id');
                $table->integer('semester');
                $table->float('nilai_akhir')->default(0);
                $table->text('deskripsi_tertinggi')->nullable();
                $table->text('deskripsi_terendah')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_rapor_akhir');
    }
};
