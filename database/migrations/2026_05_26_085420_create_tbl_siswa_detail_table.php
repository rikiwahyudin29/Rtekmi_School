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
        Schema::create('tbl_siswa_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->unique('siswa_id');
            $table->string('nama_panggilan', 50)->nullable();
            $table->integer('jml_saudara_kandung')->nullable()->default(0);
            $table->integer('jml_saudara_tiri')->nullable()->default(0);
            $table->integer('jml_saudara_angkat')->nullable()->default(0);
            $table->enum('status_yatim_piatu', ['-', 'Yatim', 'Piatu', 'Yatim Piatu'])->nullable()->default('-');
            $table->string('bahasa_sehari_hari', 50)->nullable()->default('Bahasa Indonesia');
            $table->enum('tinggal_bersama', ['Orang Tua', 'Wali', 'Asrama', 'Kos', 'Lainnya'])->nullable()->default('Orang Tua');
            $table->string('jarak_ke_sekolah', 20)->nullable();
            $table->string('transportasi', 50)->nullable();
            $table->string('no_sttb_smp', 100)->nullable();
            $table->date('tgl_sttb_smp')->nullable();
            $table->string('lama_belajar_smp', 20)->nullable()->default('3 Tahun');
            $table->text('alasan_pindah')->nullable();
            $table->string('pendidikan_ayah', 50)->nullable();
            $table->string('pendidikan_ibu', 50)->nullable();
            $table->string('penghasilan_ayah', 50)->nullable();
            $table->string('penghasilan_ibu', 50)->nullable();
            $table->string('pendidikan_wali', 50)->nullable();
            $table->string('penghasilan_wali', 50)->nullable();
            $table->string('gol_darah', 5)->nullable()->default('-');
            $table->integer('tinggi_badan')->nullable()->default(0);
            $table->integer('berat_badan')->nullable()->default(0);
            $table->text('penyakit_pernah_diderita')->nullable();
            $table->string('kelainan_jasmani', 100)->nullable()->default('-');
            $table->text('hobi')->nullable();
            $table->text('riwayat_beasiswa')->nullable();
            $table->date('tgl_meninggalkan')->nullable();
            $table->string('alasan_meninggalkan', 100)->nullable();
            $table->string('no_ijazah_smk', 100)->nullable();
            $table->integer('berat_meninggalkan')->nullable()->default(0);
            $table->integer('tinggi_meninggalkan')->nullable()->default(0);
            $table->date('tgl_diterima')->nullable();
            $table->string('iq', 20)->nullable();
            $table->string('tgl_tes_iq', 100)->nullable();
            $table->text('kepribadian')->nullable();
            $table->text('bakat_prestasi')->nullable();
            $table->text('prestasi_siswa')->nullable();
            $table->text('penerimaan_siswa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_siswa_detail');
    }
};
