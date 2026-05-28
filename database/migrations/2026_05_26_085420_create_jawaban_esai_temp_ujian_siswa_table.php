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
        Schema::create('jawaban_esai_temp_ujian_siswa', function (Blueprint $table) {
            $table->integer('ujian_id')->index('ujian_id');
            $table->integer('soal_id');
            $table->longText('esai')->nullable();
            $table->double('nilai')->nullable()->default(0);
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_esai_temp_ujian_siswa');
    }
};
