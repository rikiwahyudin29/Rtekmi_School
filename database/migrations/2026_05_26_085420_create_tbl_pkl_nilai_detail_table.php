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
        Schema::create('tbl_pkl_nilai_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pkl_id');
            $table->enum('kategori', ['Sikap', 'Pengetahuan', 'Keterampilan']);
            $table->string('aspek');
            $table->integer('skor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pkl_nilai_detail');
    }
};
