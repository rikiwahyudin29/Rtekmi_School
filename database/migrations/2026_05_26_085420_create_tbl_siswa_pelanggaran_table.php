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
        Schema::create('tbl_siswa_pelanggaran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->nullable();
            $table->integer('pelanggaran_id')->nullable();
            $table->integer('pelapor_id')->nullable();
            $table->dateTime('tanggal')->nullable()->useCurrent();
            $table->text('catatan')->nullable();
            $table->enum('status', ['Baru', 'Panggil Ortu', 'Selesai'])->nullable()->default('Baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_siswa_pelanggaran');
    }
};
