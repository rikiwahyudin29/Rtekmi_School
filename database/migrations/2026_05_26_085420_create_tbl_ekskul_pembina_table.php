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
        Schema::create('tbl_ekskul_pembina', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ekskul_id');
            $table->integer('guru_id');
            $table->enum('status', ['Aktif', 'Nonaktif'])->nullable()->default('Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ekskul_pembina');
    }
};
