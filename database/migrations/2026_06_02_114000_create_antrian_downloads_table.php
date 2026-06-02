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
        Schema::create('tbl_antrian_downloads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('tipe', 50); // skl / transkrip
            $table->string('status', 50)->default('antri'); // antri / proses
            $table->timestamps();

            // Foreign key to tbl_siswa (assuming id is unsignedBigInteger or bigint)
            // If it's string, we wouldn't use foreign. Just leave it as integer indexing for now.
            $table->index(['siswa_id', 'tipe', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_antrian_downloads');
    }
};
