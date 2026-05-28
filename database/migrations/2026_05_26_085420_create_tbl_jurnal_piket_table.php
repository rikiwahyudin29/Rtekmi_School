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
        Schema::create('tbl_jurnal_piket', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('tanggal')->nullable();
            $table->integer('guru_id')->nullable();
            $table->string('keterangan', 100)->nullable();
            $table->text('tugas')->nullable();
            $table->integer('guru_pengganti_id')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jurnal_piket');
    }
};
