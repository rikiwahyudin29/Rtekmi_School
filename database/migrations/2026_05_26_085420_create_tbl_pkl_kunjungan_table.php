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
        Schema::create('tbl_pkl_kunjungan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('guru_id');
            $table->integer('dudi_id');
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->string('foto_kunjungan')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_kunjungan');
    }
};
