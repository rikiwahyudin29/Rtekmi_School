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
        Schema::table('tbl_pembagian_tugas', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_pembagian_tugas', 'beban_jam')) {
                $table->integer('beban_jam')->default(0)->after('id_guru')->comment('Jumlah JP seminggu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pembagian_tugas', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_pembagian_tugas', 'beban_jam')) {
                $table->dropColumn('beban_jam');
            }
        });
    }
};
