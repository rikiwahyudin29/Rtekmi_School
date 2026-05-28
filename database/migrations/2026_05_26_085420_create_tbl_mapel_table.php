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
        Schema::create('tbl_mapel', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('dapodik_id', 50)->nullable();
            $table->string('nama_mapel', 100);
            $table->string('kode_mapel', 20)->nullable();
            $table->string('kelompok', 2)->nullable();
            $table->string('jurusan_id')->nullable()->default('0')->comment('0=Semua, 1,2=Multi Jurusan');
            $table->boolean('tampil_raport')->nullable()->default(true);
            $table->boolean('tampil_skl')->nullable()->default(true);
            $table->boolean('tampil_transkrip')->nullable()->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mapel');
    }
};
