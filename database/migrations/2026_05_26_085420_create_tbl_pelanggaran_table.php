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
        Schema::create('tbl_pelanggaran', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id');
            $table->text('jenis_pelanggaran');
            $table->integer('poin')->nullable()->default(0);
            $table->enum('status', ['belum', 'sudah'])->nullable()->default('belum');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pelanggaran');
    }
};
