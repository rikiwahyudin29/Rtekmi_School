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
        Schema::create('tbl_pembagian_tugas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_tahun_ajaran');
            $table->integer('id_kelas');
            $table->integer('id_mapel');
            $table->integer('id_guru');
            $table->timestamps();
            
            // Note: Since existing tables use integer ID instead of unsignedBigInteger, 
            // we will not enforce strict foreign keys at DB level to avoid mismatch errors.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pembagian_tugas');
    }
};
