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
        Schema::create('draft_ujian', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('bank_soal_id')->index('bank_soal_id');
            $table->integer('mapel_id')->nullable();
            $table->string('nama');
            $table->text('order_soal')->nullable();
            $table->integer('visible_pg')->nullable()->default(0);
            $table->integer('visible_pgmulti')->nullable()->default(0);
            $table->integer('visible_pgtf')->nullable()->default(0);
            $table->integer('visible_pgcouple')->nullable()->default(0);
            $table->integer('visible_esai')->nullable()->default(0);
            $table->integer('visible_shortentry')->nullable()->default(0);
            $table->double('bobot_tf')->nullable()->default(0);
            $table->double('bobot_esai')->nullable()->default(0);
            $table->boolean('acak_soal')->nullable()->default(false);
            $table->boolean('acak_opsi')->nullable()->default(false);
            $table->integer('timeout')->default(0);
            $table->integer('minFinishTime')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_ujian');
    }
};
