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
        Schema::create('tbl_kelas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('dapodik_id', 50)->nullable();
            $table->string('nama_kelas', 50);
            $table->integer('tingkat')->nullable();
            $table->integer('id_jurusan')->nullable();
            $table->integer('guru_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kelas');
    }
};
