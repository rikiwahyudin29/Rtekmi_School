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
        Schema::create('tbl_tautan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama', 100);
            $table->string('url');
            $table->string('icon', 50)->nullable()->default('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tautan');
    }
};
