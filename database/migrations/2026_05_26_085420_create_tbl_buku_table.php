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
        Schema::create('tbl_buku', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_buku', 50)->unique('kode_buku');
            $table->string('judul');
            $table->string('pengarang', 150)->nullable();
            $table->string('penerbit', 150)->nullable();
            $table->integer('tahun_terbit')->nullable();
            $table->integer('stok_total')->nullable()->default(0);
            $table->integer('stok_tersedia')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_buku');
    }
};
