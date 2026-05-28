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
        Schema::create('tbl_filebox', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('guru_id')->nullable();
            $table->string('judul')->nullable();
            $table->enum('kategori', ['RPP', 'Silabus', 'Bahan Ajar', 'Lainnya'])->nullable();
            $table->string('nama_file')->nullable();
            $table->enum('status', ['Pending', 'Disetujui', 'Revisi'])->nullable()->default('Pending');
            $table->text('catatan_kepsek')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_filebox');
    }
};
