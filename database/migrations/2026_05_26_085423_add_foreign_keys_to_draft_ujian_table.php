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
        Schema::table('draft_ujian', function (Blueprint $table) {
            $table->foreign(['bank_soal_id'], 'fk_draft_bank')->references(['id'])->on('m_banksoal')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_ujian', function (Blueprint $table) {
            $table->dropForeign('fk_draft_bank');
        });
    }
};
