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
        Schema::create('tbl_pkl_jurnal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pkl_id')->comment('Relasi ke tbl_pkl');
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->string('foto_kegiatan')->nullable();
            $table->text('komentar_guru')->nullable();
            $table->enum('status_jurnal', ['Menunggu', 'Disetujui', 'Revisi'])->nullable()->default('Menunggu');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_jurnal');
    }
};
