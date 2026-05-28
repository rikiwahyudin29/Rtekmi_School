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
        Schema::create('soal_data', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('bank_id')->index('bank_id');
            $table->integer('jenis_soal')->comment('1=PG, 2=Esai, 3=PG Multi, 4=PG Pasangan, 5=Isian, 6=PG TF');
            $table->longText('question');
            $table->string('shortentry')->nullable();
            $table->double('true_default_point')->nullable()->default(1);
            $table->double('false_default_point')->nullable()->default(0);
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_data');
    }
};
