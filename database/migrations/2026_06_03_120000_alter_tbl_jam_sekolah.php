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
        Schema::table('tbl_jam_sekolah', function (Blueprint $table) {
            // Jam saat ini jam_masuk_akhir kita ganti fungsi/namanya menjadi batas_scan_masuk secara logika.
            // Tapi agar aman, kita tambah kolom batas_scan_masuk dan jam_masuk_mulai_terlambat
            if (!Schema::hasColumn('tbl_jam_sekolah', 'jam_masuk_mulai_terlambat')) {
                $table->time('jam_masuk_mulai_terlambat')->default('07:00:00')->after('jam_masuk_mulai');
            }
            if (!Schema::hasColumn('tbl_jam_sekolah', 'batas_scan_masuk')) {
                $table->time('batas_scan_masuk')->default('07:30:00')->after('jam_masuk_mulai_terlambat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_jam_sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_jam_sekolah', 'jam_masuk_mulai_terlambat')) {
                $table->dropColumn('jam_masuk_mulai_terlambat');
            }
            if (Schema::hasColumn('tbl_jam_sekolah', 'batas_scan_masuk')) {
                $table->dropColumn('batas_scan_masuk');
            }
        });
    }
};
