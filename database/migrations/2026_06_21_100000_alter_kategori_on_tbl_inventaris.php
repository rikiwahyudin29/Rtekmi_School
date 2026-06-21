<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tbl_inventaris MODIFY kategori VARCHAR(100) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tbl_inventaris MODIFY kategori ENUM('Elektronik', 'Mebel', 'Alat Tulis', 'Kebersihan', 'Lainnya') NULL");
    }
};
