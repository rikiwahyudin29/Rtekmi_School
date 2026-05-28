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
        Schema::create('tbl_tracer_pertanyaan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('pertanyaan');
            $table->enum('tipe', ['text', 'textarea', 'radio', 'checkbox'])->default('text');
            $table->text('opsi_jawaban')->nullable()->comment('Pisahkan dengan koma (,) jika tipe radio/checkbox');
            $table->boolean('is_required')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tracer_pertanyaan');
    }
};
