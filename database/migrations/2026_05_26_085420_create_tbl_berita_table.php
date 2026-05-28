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
        Schema::create('tbl_berita', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('slug')->unique('slug');
            $table->string('judul');
            $table->longText('isi');
            $table->string('gambar')->nullable()->default('default.jpg');
            $table->string('penulis', 100)->nullable()->default('Admin');
            $table->integer('views')->nullable()->default(0);
            $table->boolean('is_published')->nullable()->default(true);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_berita');
    }
};
