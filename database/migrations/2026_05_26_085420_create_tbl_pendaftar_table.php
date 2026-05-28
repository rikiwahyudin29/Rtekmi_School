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
        Schema::create('tbl_pendaftar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('no_pendaftaran', 20)->unique('no_pendaftaran');
            $table->dateTime('tgl_daftar')->nullable()->useCurrent();
            $table->string('nama_lengkap', 100);
            $table->string('nisn', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->enum('jk', ['L', 'P'])->nullable()->default('L');
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('asal_sekolah', 100)->nullable();
            $table->string('no_hp_siswa', 20)->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('jurusan_minat', 50)->nullable();
            $table->enum('status_pendaftaran', ['Pending', 'Diterima', 'Cadangan', 'Ditolak'])->nullable()->default('Pending');
            $table->text('catatan_admin')->nullable();
            $table->string('foto')->nullable();
            $table->string('berkas_kk')->nullable();
            $table->string('berkas_ijazah')->nullable();
            $table->string('agama', 50)->nullable();
            $table->text('alamat_jalan')->nullable();
            $table->string('rt_rw', 20)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('no_hp_ayah', 20)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('no_hp_ibu', 20)->nullable();
            $table->string('nama_wali', 100)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->string('no_hp_wali', 20)->nullable();
            $table->decimal('nilai_rata_rata', 5)->nullable()->default(0);
            $table->boolean('is_migrated')->nullable()->default(false)->comment('1 jika sudah jadi siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pendaftar');
    }
};
