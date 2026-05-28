<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_pembagian_tugas')) {
            if (!Schema::hasColumn('tbl_pembagian_tugas', 'id_tahun_ajaran')) {
                Schema::table('tbl_pembagian_tugas', function (Blueprint $table) {
                    $table->integer('id_tahun_ajaran')->after('id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_pembagian_tugas')) {
            if (Schema::hasColumn('tbl_pembagian_tugas', 'id_tahun_ajaran')) {
                Schema::table('tbl_pembagian_tugas', function (Blueprint $table) {
                    $table->dropColumn('id_tahun_ajaran');
                });
            }
        }
    }
};
