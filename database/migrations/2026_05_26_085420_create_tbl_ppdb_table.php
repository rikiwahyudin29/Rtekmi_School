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
        Schema::create('tbl_ppdb', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('no_daftar', 20)->nullable()->unique('no_daftar');
            $table->string('nisn', 20)->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama', 50)->nullable();
            $table->text('alamat_siswa')->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->decimal('nilai_rata_rata', 5)->nullable();
            $table->string('file_ijazah')->nullable();
            $table->string('file_kk')->nullable();
            $table->enum('status_seleksi', ['Pending', 'Diterima', 'Ditolak', 'Cadangan'])->nullable()->default('Pending');
            $table->timestamp('tgl_daftar')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ppdb');
    }
};
