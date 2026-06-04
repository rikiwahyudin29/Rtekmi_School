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
        Schema::table('tbl_web_profil', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_web_profil', 'spot_hero_png')) {
                $table->string('spot_hero_png')->nullable()->after('foto_kepsek');
            }
            if (!Schema::hasColumn('tbl_web_profil', 'spot_ppdb_png')) {
                $table->string('spot_ppdb_png')->nullable()->after('spot_hero_png');
            }
        });

        Schema::table('tbl_dudi', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_dudi', 'logo')) {
                $table->string('logo')->nullable()->after('nama_dudi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_web_profil', function (Blueprint $table) {
            $table->dropColumn(['spot_hero_png', 'spot_ppdb_png']);
        });

        Schema::table('tbl_dudi', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
