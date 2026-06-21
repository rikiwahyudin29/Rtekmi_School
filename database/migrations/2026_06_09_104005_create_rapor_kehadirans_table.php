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
        if (!Schema::hasTable('tbl_rapor_kehadiran')) {
            Schema::create('tbl_rapor_kehadiran', function (Blueprint $table) {
                $table->id();
                $table->integer('siswa_id');
                $table->integer('tahun_ajaran_id');
                $table->integer('semester');
                $table->integer('sakit')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('tanpa_keterangan')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_rapor_kehadiran');
    }
};
