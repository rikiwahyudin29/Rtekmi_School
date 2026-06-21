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
        Schema::create('tbl_konseling', function (Blueprint $table) {
            $table->id();
            $table->integer('siswa_id');
            $table->integer('guru_id');
            $table->date('tanggal_konseling');
            $table->enum('jenis_konseling', ['Pribadi', 'Sosial', 'Belajar', 'Karir']);
            $table->string('topik');
            $table->text('hasil_konseling')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->enum('status', ['Selesai', 'Follow-Up'])->default('Selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_konseling');
    }
};
