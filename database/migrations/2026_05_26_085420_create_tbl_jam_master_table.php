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
        Schema::create('tbl_jam_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('urutan');
            $table->string('nama_jam', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->boolean('is_istirahat')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jam_master');
    }
};
