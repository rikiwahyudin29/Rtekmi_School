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
        if (!Schema::hasColumn('tbl_disposisi', 'is_read')) {
            Schema::table('tbl_disposisi', function (Blueprint $table) {
                $table->boolean('is_read')->default(0)->after('instruksi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tbl_disposisi', 'is_read')) {
            Schema::table('tbl_disposisi', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }
    }
};
