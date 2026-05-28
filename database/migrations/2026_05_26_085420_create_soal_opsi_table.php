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
        Schema::create('soal_opsi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('soal_id')->index('soal_id');
            $table->integer('soal_couple_id')->nullable();
            $table->longText('body');
            $table->boolean('is_key')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_opsi');
    }
};
