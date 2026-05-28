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
        Schema::create('tbl_soal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_bank_soal');
            $table->string('tipe_soal', 20);
            $table->longText('pertanyaan');
            $table->string('file_audio')->nullable();
            $table->string('file_video')->nullable();
            $table->integer('bobot')->nullable()->default(1);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('file_gambar')->nullable()->comment('Nama file gambar jika ada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_soal');
    }
};
