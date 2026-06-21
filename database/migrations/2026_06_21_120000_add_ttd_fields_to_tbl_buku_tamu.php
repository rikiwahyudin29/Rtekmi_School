<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_buku_tamu', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_buku_tamu', 'ttd_piket')) {
                $table->longText('ttd_piket')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tbl_buku_tamu', 'dikonfirmasi_oleh')) {
                $table->string('dikonfirmasi_oleh', 150)->nullable()->after('ttd_piket');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_buku_tamu', function (Blueprint $table) {
            $table->dropColumn(['ttd_piket', 'dikonfirmasi_oleh']);
        });
    }
};
