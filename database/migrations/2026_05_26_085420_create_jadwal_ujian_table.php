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
        Schema::create('jadwal_ujian', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('draft_id')->index('draft_id');
            $table->integer('jenis_id')->nullable();
            $table->string('nama');
            $table->string('kode_ruang', 50)->nullable();
            $table->integer('startDateTime');
            $table->integer('endDateTime');
            $table->integer('timeout')->comment('Waktu dalam menit');
            $table->boolean('use_token')->nullable()->default(false);
            $table->boolean('status')->nullable()->default(false)->comment('0=Belum, 1=Aktif, 2=Selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian');
    }
};
