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
        Schema::create('tbl_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->nullable();
            $table->integer('mapel_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->integer('id_tahun_ajaran')->nullable();
            $table->float('tugas')->nullable()->default(0);
            $table->float('uh')->nullable()->default(0);
            $table->float('pts')->nullable()->default(0);
            $table->float('pas')->nullable()->default(0);
            $table->float('akhir')->nullable()->default(0);
            $table->char('predikat', 1)->nullable();
            $table->text('deskripsi')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nilai');
    }
};
