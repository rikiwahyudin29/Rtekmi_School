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
        Schema::create('tbl_ekskul_absen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('jurnal_id');
            $table->integer('siswa_id');
            $table->time('waktu_scan')->nullable();
            $table->enum('status_hadir', ['Hadir', 'Sakit', 'Izin', 'Alpa'])->nullable()->default('Alpa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_absen');
    }
};
