<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_peminjaman_ruangan', function (Blueprint $table) {
            $table->id();
            $table->integer('ruangan_id');
            $table->string('peminjam', 150);
            $table->string('kegiatan', 200);
            $table->dateTime('tgl_pinjam');
            $table->dateTime('tgl_kembali');
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai'])->default('Menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_peminjaman_ruangan');
    }
};
