<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_laporan_kerusakan', function (Blueprint $table) {
            $table->id();
            $table->integer('inventaris_id');
            $table->string('pelapor', 150);
            $table->text('deskripsi');
            $table->date('tgl_lapor');
            $table->enum('status', ['Dilaporkan', 'Proses Perbaikan', 'Selesai'])->default('Dilaporkan');
            $table->text('tindakan_perbaikan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_laporan_kerusakan');
    }
};
