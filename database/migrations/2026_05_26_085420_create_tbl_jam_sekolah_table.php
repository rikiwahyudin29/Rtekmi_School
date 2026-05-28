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
        Schema::create('tbl_jam_sekolah', function (Blueprint $table) {
            $table->integer('id', true);
            $table->time('jam_masuk_mulai')->default('06:00:00');
            $table->time('jam_masuk_akhir')->default('07:15:00');
            $table->time('jam_pulang_mulai')->default('14:00:00');
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
            $table->integer('radius')->nullable()->default(100);
            $table->string('qr_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_jam_sekolah');
    }
};
