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
        Schema::create('tbl_disposisi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_surat');
            $table->integer('id_penerima');
            $table->text('instruksi');
            $table->dateTime('tanggal_disposisi')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_disposisi');
    }
};
