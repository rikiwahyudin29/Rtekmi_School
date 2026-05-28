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
        Schema::create('ujian_siswa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('jadwal_id')->index('jadwal_id');
            $table->integer('siswa_id')->index('siswa_id');
            $table->boolean('status')->nullable()->default(false)->comment('0=Belum/Sedang, 1=Selesai');
            $table->integer('pg_benar')->nullable()->default(0);
            $table->integer('pgtf_benar')->nullable()->default(0);
            $table->integer('pgmulti_benar')->nullable()->default(0);
            $table->integer('pgcouple_benar')->nullable()->default(0);
            $table->integer('shortentry_benar')->nullable()->default(0);
            $table->integer('total_pg')->nullable()->default(0);
            $table->integer('total_shortentry')->nullable()->default(0);
            $table->double('nilai_pg')->nullable()->default(0);
            $table->double('nilai_esai')->nullable()->default(0);
            $table->string('ip_address', 50)->nullable();
            $table->string('jwt')->nullable();
            $table->boolean('hasCheating')->nullable()->default(false);
            $table->integer('soal_generated')->nullable();
            $table->integer('start_at')->nullable();
            $table->integer('end_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_siswa');
    }
};
