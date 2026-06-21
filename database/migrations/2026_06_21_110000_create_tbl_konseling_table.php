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
            $table->foreignId('siswa_id')->constrained('tbl_siswa')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
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
