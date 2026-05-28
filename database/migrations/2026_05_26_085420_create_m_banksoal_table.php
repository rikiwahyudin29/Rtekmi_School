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
        Schema::create('m_banksoal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->string('kode', 100);
            $table->string('deskripsi')->nullable();
            $table->string('nama_mapel', 150);
            $table->integer('mapel_id')->nullable();
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_banksoal');
    }
};
