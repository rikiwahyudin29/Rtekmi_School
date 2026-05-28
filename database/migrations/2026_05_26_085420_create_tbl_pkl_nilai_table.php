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
        Schema::create('tbl_pkl_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pkl_id');
            $table->string('no_sertifikat', 100)->nullable();
            $table->float('nilai_sikap_rata')->nullable()->default(0);
            $table->float('nilai_pengetahuan_rata')->nullable()->default(0);
            $table->float('nilai_keterampilan_rata')->nullable()->default(0);
            $table->integer('nilai_sikap')->nullable()->default(0);
            $table->integer('nilai_teknis')->nullable()->default(0);
            $table->integer('nilai_laporan')->nullable()->default(0);
            $table->float('nilai_akhir')->nullable()->default(0);
            $table->float('nilai_rata')->nullable()->default(0);
            $table->string('predikat', 20)->nullable();
            $table->string('token_sertifikat', 100)->nullable();
            $table->dateTime('tgl_penilaian')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_nilai');
    }
};
