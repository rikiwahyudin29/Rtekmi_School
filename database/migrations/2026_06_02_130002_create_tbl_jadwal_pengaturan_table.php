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
        Schema::create('tbl_jadwal_pengaturan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_tahun_ajaran');
            $table->integer('hari_kerja')->default(5)->comment('5 hari atau 6 hari');
            $table->integer('max_jp_per_hari')->default(10)->comment('Maksimal Jam Pelajaran per hari');
            $table->boolean('izinkan_jam_ganda')->default(false)->comment('Bolehkan guru mengajar 2 kelas sekaligus?');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jadwal_pengaturan');
    }
};
