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
        Schema::create('tbl_kelulusan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->index('siswa_id');
            $table->enum('status_lulus', ['Pending', 'Lulus', 'Lulus Bersyarat', 'Tidak Lulus'])->nullable()->default('Pending');
            $table->text('catatan')->nullable();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_kelulusan');
    }
};
