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
        Schema::create('tbl_ekskul_nilai', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ekskul_id');
            $table->integer('siswa_id');
            $table->string('semester', 20);
            $table->enum('nilai_huruf', ['A', 'B', 'C', 'D']);
            $table->text('deskripsi_dapodik');
            $table->integer('persen_hadir')->default(0);
            $table->enum('layak_sertifikat', ['Y', 'N'])->nullable()->default('N');
            $table->string('token_sertifikat', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_nilai');
    }
};
