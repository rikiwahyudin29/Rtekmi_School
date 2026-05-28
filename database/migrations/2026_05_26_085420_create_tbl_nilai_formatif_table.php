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
        Schema::create('tbl_nilai_formatif', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->integer('kompetensi_id');
            $table->integer('nilai')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nilai_formatif');
    }
};
