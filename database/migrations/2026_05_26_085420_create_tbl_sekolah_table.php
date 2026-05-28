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
        Schema::create('tbl_sekolah', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('npsn', 10);
            $table->enum('status_sekolah', ['Negeri', 'Swasta'])->nullable()->default('Swasta');
            $table->string('nama_sekolah', 100)->nullable();
            $table->string('logo')->nullable()->default('default_logo.png');
            $table->string('alamat')->nullable();
            $table->string('desa_kelurahan', 50)->nullable();
            $table->string('kecamatan', 50)->nullable();
            $table->string('kabupaten', 50)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('website', 50)->nullable();
            $table->string('nip_kepsek', 30)->nullable();
            $table->string('dapodik_id', 50)->nullable();
            $table->string('kop_surat')->nullable()->default('default_kop.png');
            $table->string('ttd_kepsek')->nullable()->default('default_ttd.png');
            $table->text('slogan_sekolah')->nullable();
            $table->text('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->string('wa_api_url')->nullable();
            $table->text('wa_api_token')->nullable();
            $table->text('tele_bot_token')->nullable();
            $table->string('tele_chat_id', 100)->nullable();
            $table->text('tripay_api_key')->nullable();
            $table->text('tripay_private_key')->nullable();
            $table->string('tripay_merchant_code', 50)->nullable();
            $table->enum('mode_transaksi', ['Sandbox', 'Production'])->nullable()->default('Sandbox');
            $table->string('kelurahan', 100)->nullable();
            $table->string('koordinat_longlat', 100)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('youtube', 100)->nullable();
            $table->string('tiktok', 100)->nullable();
            $table->enum('akreditasi', ['A', 'B', 'C', 'Belum Terakreditasi', 'Unggul'])->nullable()->default('Belum Terakreditasi');
            $table->string('no_sk_pendirian', 100)->nullable();
            $table->date('tgl_sk_pendirian')->nullable();
            $table->string('nama_kepsek', 150)->nullable();
            $table->string('foto_kepsek')->nullable();
            $table->text('sambutan_kepsek')->nullable();
            $table->text('map_link')->nullable();
            $table->string('license_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_sekolah');
    }
};
