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
        Schema::create('tbl_log_keuangan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->string('nama_user', 100);
            $table->string('role', 50);
            $table->string('ip_address', 50);
            $table->string('lokasi')->nullable();
            $table->text('device_info')->nullable();
            $table->text('aksi');
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_log_keuangan');
    }
};
