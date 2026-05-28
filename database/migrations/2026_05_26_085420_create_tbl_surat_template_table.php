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
        Schema::create('tbl_surat_template', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_template', 100)->nullable();
            $table->string('kode_jenis', 50)->nullable();
            $table->string('format_nomor', 100)->nullable();
            $table->text('isi_html')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_surat_template');
    }
};
