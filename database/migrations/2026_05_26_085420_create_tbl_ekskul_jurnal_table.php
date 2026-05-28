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
        Schema::create('tbl_ekskul_jurnal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ekskul_id');
            $table->integer('pembina_id');
            $table->date('tanggal');
            $table->text('materi_kegiatan');
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_jurnal');
    }
};
