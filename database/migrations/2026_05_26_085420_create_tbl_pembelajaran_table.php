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
        Schema::create('tbl_pembelajaran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('tahun_ajaran', 20);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->integer('kelas_id');
            $table->integer('mapel_id');
            $table->integer('guru_id');
            $table->integer('kkm')->default(75)->comment('KKM (K13) atau KKTP (Kurmer)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pembelajaran');
    }
};
