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
        Schema::table('tbl_jenis_bayar', function (Blueprint $table) {
            $table->boolean('is_beda_gender')->default(false)->after('is_per_jurusan');
            $table->double('nominal_putri_default')->nullable()->default(0)->after('nominal_default');
        });

        Schema::table('tbl_jenis_bayar_jurusan', function (Blueprint $table) {
            $table->bigInteger('nominal_putri')->default(0)->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_jenis_bayar_jurusan', function (Blueprint $table) {
            $table->dropColumn('nominal_putri');
        });

        Schema::table('tbl_jenis_bayar', function (Blueprint $table) {
            $table->dropColumn('nominal_putri_default');
            $table->dropColumn('is_beda_gender');
        });
    }
};
