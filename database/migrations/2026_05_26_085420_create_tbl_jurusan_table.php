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
        Schema::create('tbl_jurusan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('dapodik_id', 50)->nullable();
            $table->string('kode_jurusan', 10);
            $table->string('nama_jurusan', 100);
            $table->integer('kepala_jurusan_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jurusan');
    }
};
