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
        Schema::create('tbl_setting_kelulusan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('tgl_pengumuman')->nullable();
            $table->string('nomor_surat', 100)->nullable();
            $table->string('titimangsa', 100)->nullable();
            $table->text('pembuka_surat')->nullable();
            $table->text('penutup_surat')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_setting_kelulusan');
    }
};
