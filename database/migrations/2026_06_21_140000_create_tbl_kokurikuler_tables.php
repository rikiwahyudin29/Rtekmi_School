<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_koku_kelompok')) {
            Schema::create('tbl_koku_kelompok', function (Blueprint $table) {
                $table->id();
                $table->integer('guru_id');
                $table->string('nama_kelompok');
                $table->integer('kelas_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_koku_tema')) {
            Schema::create('tbl_koku_tema', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tema');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_koku_kegiatan')) {
            Schema::create('tbl_koku_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->integer('tema_id');
                $table->string('nama_kegiatan');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_koku_nilai')) {
            Schema::create('tbl_koku_nilai', function (Blueprint $table) {
                $table->id();
                $table->integer('siswa_id');
                $table->integer('kegiatan_id');
                $table->integer('nilai')->default(0);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_koku_nilai');
        Schema::dropIfExists('tbl_koku_kegiatan');
        Schema::dropIfExists('tbl_koku_tema');
        Schema::dropIfExists('tbl_koku_kelompok');
    }
};
