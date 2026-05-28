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
        Schema::create('tbl_ekskul_anggota', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ekskul_id');
            $table->integer('siswa_id');
            $table->date('tgl_daftar');
            $table->enum('status_anggota', ['Pending', 'Approved', 'Rejected', 'Dikeluarkan'])->nullable()->default('Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_anggota');
    }
};
