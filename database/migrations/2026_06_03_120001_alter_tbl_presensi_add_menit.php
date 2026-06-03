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
        Schema::table('tbl_presensi', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_presensi', 'menit_terlambat')) {
                $table->integer('menit_terlambat')->default(0)->after('status_kehadiran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_presensi', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_presensi', 'menit_terlambat')) {
                $table->dropColumn('menit_terlambat');
            }
        });
    }
};
