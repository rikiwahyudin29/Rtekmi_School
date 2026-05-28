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
        Schema::create('tbl_transaksi_tabungan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('rekening_id');
            $table->enum('jenis_transaksi', ['Setor', 'Tarik', 'Transfer_Masuk', 'Transfer_Keluar', 'Bayar_Sekolah']);
            $table->decimal('nominal', 15);
            $table->decimal('saldo_setelah_transaksi', 15);
            $table->text('keterangan')->nullable();
            $table->integer('referensi_tujuan')->nullable();
            $table->integer('petugas_id');
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaksi_tabungan');
    }
};
