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
        Schema::create('tbl_kenaikan_kelas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->string('status', 50);
            $table->integer('kelas_tujuan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kenaikan_kelas');
    }
};
