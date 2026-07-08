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
        Schema::table('tbl_user_devices', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('last_ip');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_user_devices', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
