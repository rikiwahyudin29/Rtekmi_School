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
        Schema::table('tbl_jadwal', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_jadwal', 'id_pembagian_tugas')) {
                $table->integer('id_pembagian_tugas')->nullable()->after('id_guru')->comment('Link ke tbl_pembagian_tugas');
            }
            if (!Schema::hasColumn('tbl_jadwal', 'id_jam_master')) {
                $table->integer('id_jam_master')->nullable()->after('hari');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_jadwal', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_jadwal', 'id_pembagian_tugas')) {
                $table->dropColumn('id_pembagian_tugas');
            }
            if (Schema::hasColumn('tbl_jadwal', 'id_jam_master')) {
                $table->dropColumn('id_jam_master');
            }
        });
    }
};
