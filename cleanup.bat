@echo off
echo Membersihkan file-file sampah di folder root (utama)...
del /f /q check_columns.php
del /f /q fix_*.php
del /f /q get_migrations.php
del /f /q run_*.php
del /f /q test-*.php
del /f /q mariyadhulja.sql

echo Membersihkan file-file sampah di folder public...
cd public
del /f /q test*.php
del /f /q fix.php
del /f /q investigate.php
del /f /q db_test.php
del /f /q log_test2.php
cd ..

echo Pembersihan total selesai!
pause
