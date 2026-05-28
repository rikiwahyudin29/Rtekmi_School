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
        Schema::create('tbl_web_profil', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('deskripsi_hero')->nullable()->comment('Teks di banner depan');
            $table->string('nama_kepsek', 100)->nullable();
            $table->text('sambutan_kepsek')->nullable();
            $table->string('foto_kepsek')->nullable();
            $table->string('link_fb')->nullable();
            $table->string('link_ig')->nullable();
            $table->string('link_yt')->nullable();
            $table->text('link_map')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_web_profil');
    }
};
