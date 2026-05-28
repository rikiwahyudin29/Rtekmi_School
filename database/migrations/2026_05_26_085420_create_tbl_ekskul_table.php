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
        Schema::create('tbl_ekskul', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_ekskul', 100);
            $table->enum('kategori', ['Wajib', 'Pilihan'])->default('Pilihan');
            $table->string('hari', 50)->nullable();
            $table->string('jam', 50)->nullable();
            $table->text('visi_misi')->nullable();
            $table->integer('kuota')->nullable();
            $table->string('logo')->nullable()->default('default_ekskul.png');
            $table->enum('status', ['Aktif', 'Nonaktif'])->nullable()->default('Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul');
    }
};
