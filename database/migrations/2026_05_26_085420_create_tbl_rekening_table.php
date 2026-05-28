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
        Schema::create('tbl_rekening', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('siswa_id')->unique('siswa_id');
            $table->string('pin');
            $table->decimal('saldo', 15)->default(0);
            $table->enum('status_rekening', ['Aktif', 'Blokir'])->nullable()->default('Aktif');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_rekening');
    }
};
