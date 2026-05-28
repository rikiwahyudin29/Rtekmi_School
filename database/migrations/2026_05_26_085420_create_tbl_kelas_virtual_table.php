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
        Schema::create('tbl_kelas_virtual', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('guru_id')->nullable();
            $table->integer('mapel_id')->nullable();
            $table->string('hari', 20)->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->string('judul_pertemuan')->nullable();
            $table->dateTime('tgl_pertemuan')->nullable();
            $table->text('link_meet')->nullable();
            $table->enum('status', ['Aktif', 'Selesai'])->nullable()->default('Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kelas_virtual');
    }
};
