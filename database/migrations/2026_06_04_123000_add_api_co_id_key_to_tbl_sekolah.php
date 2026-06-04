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
        Schema::table('tbl_sekolah', function (Blueprint $table) {
            $table->string('api_co_id_key')->nullable()->after('wa_api_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_sekolah', function (Blueprint $table) {
            $table->dropColumn('api_co_id_key');
        });
    }
};
