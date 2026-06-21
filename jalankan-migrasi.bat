@echo off
echo Menjalankan Migrasi File Baru Satu per Satu...

php artisan migrate --path=database/migrations/2026_06_21_015044_create_tbl_jadwal_piket_table.php
php artisan migrate --path=database/migrations/2026_06_21_015045_create_tbl_buku_tamu_table.php
php artisan migrate --path=database/migrations/2026_06_21_015945_add_piket_fields_to_tbl_buku_tamu.php
php artisan migrate --path=database/migrations/2026_06_21_090000_create_tbl_pkl_kelas_table.php
php artisan migrate --path=database/migrations/2026_06_21_100000_alter_kategori_on_tbl_inventaris.php
php artisan migrate --path=database/migrations/2026_06_21_100001_create_tbl_peminjaman_ruangan_table.php
php artisan migrate --path=database/migrations/2026_06_21_100002_create_tbl_laporan_kerusakan_table.php
php artisan migrate --path=database/migrations/2026_06_21_110000_create_tbl_konseling_table.php
php artisan migrate --path=database/migrations/2026_06_21_120000_add_ttd_fields_to_tbl_buku_tamu.php
php artisan migrate --path=database/migrations/2026_06_21_130000_add_is_read_to_tbl_disposisi_table.php
php artisan migrate --path=database/migrations/2026_06_21_140000_create_tbl_kokurikuler_tables.php

echo Selesai!
pause
