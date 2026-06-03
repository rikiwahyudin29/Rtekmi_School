<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom is_per_jurusan
        Schema::table('tbl_jenis_bayar', function (Blueprint $table) {
            $table->boolean('is_per_jurusan')->default(false)->after('tipe_bayar');
        });

        // 2. Buat tabel tbl_jenis_bayar_jurusan
        Schema::create('tbl_jenis_bayar_jurusan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_jenis_bayar');
            $table->unsignedBigInteger('id_jurusan');
            $table->bigInteger('nominal')->default(0);
            $table->timestamps();

            // Foreign keys opsional (tergantung strict tidaknya database)
            // $table->foreign('id_jenis_bayar')->references('id')->on('tbl_jenis_bayar')->onDelete('cascade');
            // $table->foreign('id_jurusan')->references('id')->on('tbl_jurusan')->onDelete('cascade');
        });

        // 3. One-time script fix: Sinkronisasi Siswa Jurusan ID dengan Kelas
        DB::statement("
            UPDATE tbl_siswa s
            JOIN tbl_kelas k ON s.kelas_id = k.id
            SET s.jurusan_id = k.id_jurusan
            WHERE s.kelas_id IS NOT NULL AND k.id_jurusan IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jenis_bayar_jurusan');
        Schema::table('tbl_jenis_bayar', function (Blueprint $table) {
            $table->dropColumn('is_per_jurusan');
        });
    }
};
