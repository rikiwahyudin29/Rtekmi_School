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
        Schema::create('tbl_pkl_absen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pkl_id')->comment('Relasi ke tbl_pkl');
            $table->date('tanggal');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->string('lat_masuk', 50)->nullable();
            $table->string('long_masuk', 50)->nullable();
            $table->string('lat_pulang', 50)->nullable();
            $table->string('long_pulang', 50)->nullable();
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpa'])->nullable()->default('Hadir');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_absen');
    }
};
