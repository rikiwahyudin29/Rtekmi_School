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
        Schema::create('tbl_nilai_sumatif', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->integer('id_kelas');
            $table->integer('id_mapel');
            $table->enum('jenis_ujian', ['STS', 'SAS'])->comment('Sumatif Tengah/Akhir Semester');
            $table->integer('nilai')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nilai_sumatif');
    }
};
