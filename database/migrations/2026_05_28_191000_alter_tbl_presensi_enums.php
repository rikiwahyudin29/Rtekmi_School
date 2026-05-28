<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah kolom ENUM menjadi VARCHAR agar kompatibel dengan input dari Android (seperti 'Hadir', 'QRCode/Geo', 'Terverifikasi')
        DB::statement("ALTER TABLE tbl_presensi MODIFY COLUMN status_kehadiran VARCHAR(50) NULL DEFAULT 'Alpha'");
        DB::statement("ALTER TABLE tbl_presensi MODIFY COLUMN metode VARCHAR(50) NULL DEFAULT 'Online'");
        DB::statement("ALTER TABLE tbl_presensi MODIFY COLUMN status_verifikasi VARCHAR(50) NULL DEFAULT 'Disetujui'");
    }

    public function down(): void
    {
        // Rollback (tidak dikembalikan ke enum karena berisiko data hilang, biarkan tetap varchar)
    }
};
