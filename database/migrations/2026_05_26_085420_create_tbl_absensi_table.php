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
        Schema::create('tbl_absensi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('tgl');
            $table->enum('status', ['H', 'S', 'I', 'A'])->comment('Hadir, Sakit, Izin, Alpha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_absensi');
    }
};
