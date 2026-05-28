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
        Schema::create('tbl_kenaikan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kelas_id');
            $table->integer('siswa_id');
            $table->integer('status')->default(1)->comment('1:Naik, 2:Tinggal, 3:Lulus');
            $table->string('nama_kelas', 100)->nullable()->comment('Contoh: XI RPL 1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kenaikan');
    }
};
