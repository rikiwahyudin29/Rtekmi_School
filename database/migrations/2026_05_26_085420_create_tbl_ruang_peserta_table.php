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
        Schema::create('tbl_ruang_peserta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_siswa')->unique('unik_peserta');
            $table->integer('id_ruangan');
            $table->integer('id_sesi');
            $table->string('no_komputer', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ruang_peserta');
    }
};
