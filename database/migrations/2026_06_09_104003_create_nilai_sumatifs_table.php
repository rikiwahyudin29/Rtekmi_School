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
        if (!Schema::hasTable('tbl_nilai_sumatif')) {
            Schema::create('tbl_nilai_sumatif', function (Blueprint $table) {
                $table->id();
                $table->integer('mapel_id');
                $table->integer('guru_id');
                $table->integer('siswa_id');
                $table->string('jenis'); // 'STS' atau 'SAS'
                $table->float('nilai')->default(0);
                $table->integer('semester');
                $table->integer('tahun_ajaran_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nilai_sumatif');
    }
};
