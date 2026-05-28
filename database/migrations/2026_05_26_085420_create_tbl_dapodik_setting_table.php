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
        Schema::create('tbl_dapodik_setting', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('npsn', 20);
            $table->string('ip_dapodik', 100)->comment('Contoh: http://192.168.1.5:5774');
            $table->string('key_integrasi');
            $table->enum('status_koneksi', ['Terhubung', 'Gagal'])->nullable()->default('Gagal');
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_dapodik_setting');
    }
};
