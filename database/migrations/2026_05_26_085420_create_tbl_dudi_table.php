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
        Schema::create('tbl_dudi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama_dudi');
            $table->string('bidang_usaha', 100)->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('nama_pimpinan', 150)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->integer('radius_absen')->nullable()->default(50)->comment('Radius dalam satuan meter');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_dudi');
    }
};
