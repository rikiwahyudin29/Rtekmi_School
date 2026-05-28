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
        Schema::create('tbl_master_pelanggaran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_pelanggaran')->nullable();
            $table->integer('poin')->nullable();
            $table->enum('kategori', ['Ringan', 'Sedang', 'Berat'])->nullable()->default('Ringan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_master_pelanggaran');
    }
};
