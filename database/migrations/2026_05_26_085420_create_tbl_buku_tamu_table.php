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
        Schema::create('tbl_buku_tamu', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('tanggal');
            $table->integer('no_antrian');
            $table->string('nama_lengkap', 150);
            $table->string('instansi_asal', 150);
            $table->string('no_hp', 20)->nullable();
            $table->text('keperluan');
            $table->enum('kategori', ['Umum', 'Khusus'])->default('Umum');
            $table->longText('ttd')->nullable()->comment('Simpan Base64 Gambar TTD');
            $table->enum('status', ['Menunggu', 'Selesai'])->nullable()->default('Menunggu');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_buku_tamu');
    }
};
