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
        Schema::create('tbl_surat_masuk', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat');
            $table->date('tanggal_diterima');
            $table->string('pengirim');
            $table->text('perihal');
            $table->string('file_scan')->nullable();
            $table->string('status_disposisi', 50)->nullable()->default('Belum Disposisi');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_surat_masuk');
    }
};
