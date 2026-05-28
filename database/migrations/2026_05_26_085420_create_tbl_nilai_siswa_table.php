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
        Schema::create('tbl_nilai_siswa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->index('siswa_id');
            $table->integer('mapel_id')->index('mapel_id');
            $table->double('s1')->nullable()->default(0);
            $table->double('s2')->nullable()->default(0);
            $table->double('s3')->nullable()->default(0);
            $table->double('s4')->nullable()->default(0);
            $table->double('s5')->nullable()->default(0);
            $table->double('s6')->nullable()->default(0);
            $table->double('nilai_us')->nullable()->default(0)->comment('Nilai Ujian Sekolah/Ijazah');
            $table->double('nilai_akhir')->nullable()->default(0);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nilai_siswa');
    }
};
