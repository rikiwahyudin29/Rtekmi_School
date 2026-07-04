<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_nilai_formatif', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_nilai_formatif', 'tahun_ajaran_id')) {
                $table->integer('tahun_ajaran_id')->nullable()->after('siswa_id');
            }
            if (!Schema::hasColumn('tbl_nilai_formatif', 'mapel_id')) {
                $table->integer('mapel_id')->nullable()->after('siswa_id');
            }
            if (!Schema::hasColumn('tbl_nilai_formatif', 'tp_id')) {
                // In case it's still kompetensi_id
                if (Schema::hasColumn('tbl_nilai_formatif', 'kompetensi_id')) {
                    $table->renameColumn('kompetensi_id', 'tp_id');
                } else {
                    $table->integer('tp_id')->nullable()->after('siswa_id');
                }
            }
            if (!Schema::hasColumn('tbl_nilai_formatif', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // No down needed for safety
    }
};
