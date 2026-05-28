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
        Schema::create('tbl_kehadiran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->integer('id_kelas');
            $table->integer('sakit')->nullable()->default(0);
            $table->integer('izin')->nullable()->default(0);
            $table->integer('alpa')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kehadiran');
    }
};
