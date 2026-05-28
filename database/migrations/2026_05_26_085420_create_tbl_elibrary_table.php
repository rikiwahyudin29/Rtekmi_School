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
        Schema::create('tbl_elibrary', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('judul')->nullable();
            $table->string('penulis', 100)->nullable();
            $table->string('penerbit', 100)->nullable();
            $table->year('tahun')->nullable();
            $table->string('kategori', 50)->nullable();
            $table->string('file_ebook')->nullable();
            $table->string('cover')->nullable();
            $table->integer('diakses')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_elibrary');
    }
};
