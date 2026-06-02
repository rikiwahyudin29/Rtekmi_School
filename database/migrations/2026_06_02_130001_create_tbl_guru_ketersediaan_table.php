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
        Schema::create('tbl_guru_ketersediaan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_guru');
            $table->integer('id_tahun_ajaran');
            $table->string('hari', 20); // Senin, Selasa, dll
            $table->integer('id_jam_master'); // Slot jam ke berapa
            $table->boolean('is_available')->default(false)->comment('Apakah guru ini BISA mengajar di slot ini?');
            $table->timestamps();
            
            $table->index(['id_guru', 'id_tahun_ajaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_guru_ketersediaan');
    }
};
