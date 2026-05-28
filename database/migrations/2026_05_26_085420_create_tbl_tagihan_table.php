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
        Schema::create('tbl_tagihan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_jenis_bayar')->index('id_jenis_bayar');
            $table->integer('id_siswa')->index('id_siswa');
            $table->integer('id_kelas')->nullable()->default(0);
            $table->double('nominal_tagihan')->nullable()->default(0);
            $table->double('nominal_terbayar')->nullable()->default(0);
            $table->enum('status_bayar', ['LUNAS', 'BELUM', 'CICIL'])->nullable()->default('BELUM');
            $table->string('keterangan', 50)->nullable();
            $table->integer('bulan_ke')->nullable()->default(0);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tagihan');
    }
};
