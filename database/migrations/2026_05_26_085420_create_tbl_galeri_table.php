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
        Schema::create('tbl_galeri', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('judul')->nullable();
            $table->string('gambar');
            $table->string('kategori', 50)->nullable()->default('Kegiatan');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_galeri');
    }
};
