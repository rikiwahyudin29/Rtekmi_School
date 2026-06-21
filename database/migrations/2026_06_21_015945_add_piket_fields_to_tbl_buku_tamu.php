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
        Schema::table('tbl_buku_tamu', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_buku_tamu', 'bertemu_dengan')) {
                $table->string('bertemu_dengan', 150)->nullable();
            }
            if (!Schema::hasColumn('tbl_buku_tamu', 'jam_datang')) {
                $table->time('jam_datang')->nullable();
            }
            if (!Schema::hasColumn('tbl_buku_tamu', 'jam_pulang')) {
                $table->time('jam_pulang')->nullable();
            }
            if (!Schema::hasColumn('tbl_buku_tamu', 'pencatat_id')) {
                $table->integer('pencatat_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_buku_tamu', function (Blueprint $table) {
            $table->dropColumn(['bertemu_dengan', 'jam_datang', 'jam_pulang', 'pencatat_id']);
        });
    }
};
