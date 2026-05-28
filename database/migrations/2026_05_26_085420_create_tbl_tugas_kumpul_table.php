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
        Schema::create('tbl_tugas_kumpul', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tugas_id')->index('tugas_id');
            $table->integer('siswa_id')->index('siswa_id');
            $table->string('file_jawaban')->nullable();
            $table->text('catatan_siswa')->nullable();
            $table->integer('nilai')->nullable();
            $table->text('komentar_guru')->nullable();
            $table->dateTime('tgl_kumpul')->nullable()->useCurrent();
            $table->enum('status_kumpul', ['Tepat Waktu', 'Terlambat'])->nullable()->default('Tepat Waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tugas_kumpul');
    }
};
