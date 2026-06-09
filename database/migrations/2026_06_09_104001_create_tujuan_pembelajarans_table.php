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
        Schema::create('tbl_tujuan_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('tbl_mapel')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('tbl_guru')->onDelete('cascade');
            $table->string('kode_tp');
            $table->text('deskripsi');
            $table->string('semester');
            $table->string('tingkat'); // Misal: Fase E, Fase F, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tujuan_pembelajaran');
    }
};
