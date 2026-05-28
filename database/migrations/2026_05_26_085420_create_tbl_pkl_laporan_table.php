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
        Schema::create('tbl_pkl_laporan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pkl_id');
            $table->string('file_laporan');
            $table->enum('status_laporan', ['Menunggu', 'Revisi', 'Disetujui'])->nullable()->default('Menunggu');
            $table->text('catatan_revisi')->nullable();
            $table->dateTime('tgl_upload')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_laporan');
    }
};
