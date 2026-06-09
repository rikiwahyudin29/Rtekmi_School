<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // MODUL P5 (PROJEK PENGUATAN PROFIL PELAJAR PANCASILA)
        // ============================================

        Schema::create('tbl_p5_tema', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_tema', 255);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_p5_projek', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tema_id');
            $table->string('nama_projek', 255);
            $table->text('deskripsi_projek')->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_p5_dimensi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_dimensi', 255);
            $table->timestamps();
        });

        Schema::create('tbl_p5_elemen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('dimensi_id');
            $table->string('nama_elemen', 255);
            $table->timestamps();
        });

        Schema::create('tbl_p5_sub_elemen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('elemen_id');
            $table->text('deskripsi_sub_elemen');
            $table->string('fase', 50)->default('Fase E');
            $table->timestamps();
        });

        Schema::create('tbl_p5_kelompok', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_kelompok', 255);
            $table->integer('projek_id');
            $table->integer('guru_koordinator_id');
            $table->string('kelas_id_list', 255)->comment('Comma separated kelas id');
            $table->timestamps();
        });

        Schema::create('tbl_p5_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kelompok_id');
            $table->integer('siswa_id');
            $table->integer('sub_elemen_id');
            $table->enum('nilai', ['MB', 'SB', 'BSH', 'SAB']);
            $table->timestamps();
        });

        Schema::create('tbl_p5_catatan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kelompok_id');
            $table->integer('siswa_id');
            $table->text('catatan_proses')->nullable();
            $table->timestamps();
        });

        // ============================================
        // MODUL UKK & SKILL PASSPORT
        // ============================================

        Schema::create('tbl_skkni', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_unit', 50);
            $table->string('judul_unit', 255);
            $table->integer('jurusan_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_asesor', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_asesor', 255);
            $table->string('no_sertifikat', 100)->nullable();
            $table->integer('dudi_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_ukk_paket', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_paket', 50);
            $table->string('nama_paket', 255);
            $table->integer('jurusan_id');
            $table->timestamps();
        });

        Schema::create('tbl_skill_passport', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->integer('skkni_id');
            $table->enum('status', ['K', 'BK'])->comment('Kompeten, Belum Kompeten');
            $table->integer('guru_penilai_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tbl_ukk_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->integer('paket_id');
            $table->integer('asesor_internal_id')->nullable();
            $table->integer('asesor_eksternal_id')->nullable();
            $table->decimal('nilai_pengetahuan', 5, 2)->nullable();
            $table->decimal('nilai_keterampilan', 5, 2)->nullable();
            $table->enum('kesimpulan', ['Sangat Baik', 'Baik', 'Cukup', 'Belum Kompeten'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ukk_nilai');
        Schema::dropIfExists('tbl_skill_passport');
        Schema::dropIfExists('tbl_ukk_paket');
        Schema::dropIfExists('tbl_asesor');
        Schema::dropIfExists('tbl_skkni');
        
        Schema::dropIfExists('tbl_p5_catatan');
        Schema::dropIfExists('tbl_p5_nilai');
        Schema::dropIfExists('tbl_p5_kelompok');
        Schema::dropIfExists('tbl_p5_sub_elemen');
        Schema::dropIfExists('tbl_p5_elemen');
        Schema::dropIfExists('tbl_p5_dimensi');
        Schema::dropIfExists('tbl_p5_projek');
        Schema::dropIfExists('tbl_p5_tema');
    }
};
