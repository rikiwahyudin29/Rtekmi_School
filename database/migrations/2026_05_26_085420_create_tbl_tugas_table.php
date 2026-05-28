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
        Schema::create('tbl_tugas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('guru_id')->index('guru_id');
            $table->integer('mapel_id')->index('mapel_id');
            $table->integer('kelas_id')->index('kelas_id');
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->string('file_pendukung')->nullable();
            $table->dateTime('deadline');
            $table->integer('status')->nullable()->default(1);
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tugas');
    }
};
