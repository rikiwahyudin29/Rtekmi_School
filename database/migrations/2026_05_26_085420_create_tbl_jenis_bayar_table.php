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
        Schema::create('tbl_jenis_bayar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_pos_bayar');
            $table->integer('id_tahun_ajaran');
            $table->enum('tipe_bayar', ['BULANAN', 'BEBAS'])->comment('Bulanan=SPP, Bebas=Sekali Bayar');
            $table->double('nominal_default')->nullable()->default(0);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jenis_bayar');
    }
};
