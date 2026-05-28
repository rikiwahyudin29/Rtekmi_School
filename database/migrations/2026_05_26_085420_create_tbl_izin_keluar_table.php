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
        Schema::create('tbl_izin_keluar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->nullable();
            $table->text('alasan')->nullable();
            $table->dateTime('waktu_keluar')->nullable();
            $table->dateTime('waktu_kembali')->nullable();
            $table->enum('status', ['Keluar', 'Kembali'])->nullable()->default('Keluar');
            $table->integer('pencatat_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_izin_keluar');
    }
};
