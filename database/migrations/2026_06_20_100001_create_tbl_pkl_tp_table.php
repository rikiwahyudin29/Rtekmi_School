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
        if (!Schema::hasTable('tbl_pkl_tp')) {
            Schema::create('tbl_pkl_tp', function (Blueprint $table) {
                $table->id();
                $table->integer('kelompok_id');
                $table->string('kode_tp');
                $table->text('deskripsi');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_tp');
    }
};
