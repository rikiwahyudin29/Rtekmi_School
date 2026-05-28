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
        Schema::create('tbl_set_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('mapel_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->integer('p_tugas')->nullable()->default(20);
            $table->integer('p_uh')->nullable()->default(30);
            $table->integer('p_pts')->nullable()->default(25);
            $table->integer('p_pas')->nullable()->default(25);
            $table->integer('kkm')->nullable()->default(75);
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_set_nilai');
    }
};
