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
        Schema::create('tbl_set_sp', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sp_1')->nullable()->default(75);
            $table->integer('sp_2')->nullable()->default(50);
            $table->integer('sp_3')->nullable()->default(25);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_set_sp');
    }
};
