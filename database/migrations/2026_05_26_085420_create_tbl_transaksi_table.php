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
        Schema::create('tbl_transaksi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_transaksi', 20)->nullable();
            $table->string('merchant_ref', 50)->nullable();
            $table->string('reference', 50)->nullable();
            $table->integer('id_tagihan')->index('id_tagihan');
            $table->integer('id_siswa');
            $table->double('jumlah_bayar');
            $table->double('fee_admin')->nullable()->default(0);
            $table->double('total_bayar')->nullable()->default(0);
            $table->string('status_transaksi', 20)->nullable()->default('SUCCESS');
            $table->text('checkout_url')->nullable();
            $table->string('payment_type', 50)->nullable()->default('TUNAI');
            $table->dateTime('tanggal_bayar')->nullable()->useCurrent();
            $table->integer('petugas_id')->nullable()->default(0);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaksi');
    }
};
