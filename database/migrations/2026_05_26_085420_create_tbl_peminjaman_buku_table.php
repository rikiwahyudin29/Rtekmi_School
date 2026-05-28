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
        Schema::create('tbl_peminjaman_buku', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_siswa');
            $table->integer('id_buku');
            $table->date('tgl_pinjam');
            $table->date('tgl_kembali_seharusnya');
            $table->date('tgl_dikembalikan')->nullable();
            $table->string('status', 50)->nullable()->default('Dipinjam');
            $table->integer('denda')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_peminjaman_buku');
    }
};
