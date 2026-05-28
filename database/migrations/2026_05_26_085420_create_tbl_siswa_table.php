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
        Schema::create('tbl_siswa', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_user')->nullable();
            $table->string('dapodik_id', 50)->nullable();
            $table->string('rombel_id_dapodik', 50)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->integer('jurusan_id')->nullable();
            $table->string('nisn', 20)->unique('nisn');
            $table->string('nis', 20)->nullable();
            $table->string('rfid_uid', 50)->nullable();
            $table->string('qr_code', 100)->nullable();
            $table->string('nama_lengkap', 100);
            $table->enum('jk', ['L', 'P'])->nullable()->default('L');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->default('L');
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('status_keluarga', 50)->nullable()->default('Anak Kandung');
            $table->integer('anak_ke')->nullable();
            $table->text('alamat')->nullable();
            $table->string('sekolah_asal', 100)->nullable();
            $table->string('diterima_kelas', 20)->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->string('tahun_angkatan', 10)->nullable()->default('-');
            $table->string('no_hp_siswa', 20)->nullable();
            $table->string('email_siswa', 100)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('nama_wali', 100)->nullable();
            $table->string('pekerjaan_wali', 50)->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('pekerjaan_ayah', 50)->nullable();
            $table->string('pekerjaan_ibu', 50)->nullable();
            $table->enum('status_siswa', ['Aktif', 'Lulus', 'Keluar', 'Skorsing'])->nullable()->default('Aktif');
            $table->string('foto')->nullable()->default('default.png');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('nik', 20)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->integer('agama_id')->nullable();
            $table->string('password')->nullable();
            $table->string('role', 20)->nullable()->default('siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_siswa');
    }
};
