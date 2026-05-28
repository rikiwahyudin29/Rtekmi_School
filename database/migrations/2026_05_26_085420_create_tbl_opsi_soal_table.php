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
        Schema::create('tbl_opsi_soal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_soal');
            $table->string('kode_opsi', 50)->nullable();
            $table->longText('teks_opsi');
            $table->boolean('is_benar')->nullable()->default(false);
            $table->string('pasangan_uuid', 100)->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_opsi_soal');
    }
};
