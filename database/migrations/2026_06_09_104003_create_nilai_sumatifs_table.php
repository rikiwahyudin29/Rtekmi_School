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
            $table->id();
            $table->foreignId('mapel_id')->constrained('tbl_mapel')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('tbl_guru')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('tbl_siswa')->onDelete('cascade');
            $table->string('jenis'); // 'STS' atau 'SAS'
            $table->float('nilai')->default(0);
            $table->integer('semester');
            $table->foreignId('tahun_ajaran_id')->constrained('tbl_tahun_ajaran')->onDelete('cascade');
            $table->timestamps();
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
