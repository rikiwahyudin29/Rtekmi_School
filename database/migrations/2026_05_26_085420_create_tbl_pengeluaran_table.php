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
        Schema::create('tbl_pengeluaran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_divisi')->index('id_divisi');
            $table->integer('id_jenis')->index('id_jenis');
            $table->string('judul_pengeluaran');
            $table->double('nominal');
            $table->timestamp('tanggal')->nullable()->useCurrent();
            $table->text('keterangan')->nullable();
            $table->integer('petugas_id')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pengeluaran');
    }
};
