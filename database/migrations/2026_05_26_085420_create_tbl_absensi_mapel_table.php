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
        Schema::create('tbl_absensi_mapel', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_jurnal')->index('id_jurnal');
            $table->integer('id_siswa')->index('id_siswa');
            $table->enum('status', ['H', 'S', 'I', 'A'])->nullable()->default('H');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_absensi_mapel');
    }
};
