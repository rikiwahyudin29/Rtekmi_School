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
        Schema::create('tbl_inventaris', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_barang', 50)->nullable();
            $table->string('nama_barang', 100)->nullable();
            $table->enum('kategori', ['Elektronik', 'Mebel', 'Alat Tulis', 'Kebersihan', 'Lainnya'])->nullable();
            $table->string('lokasi', 100)->nullable();
            $table->integer('jumlah')->nullable();
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->nullable()->default('Baik');
            $table->date('tgl_masuk')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_inventaris');
    }
};
