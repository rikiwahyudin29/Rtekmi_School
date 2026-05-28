# Checklist Implementasi: Input Nilai Raport

- [x] Membuat Model dan Migration untuk tabel `nilai_raports`.
- [x] Menambahkan rute `guru/nilai-raport` ke `routes/web.php`.
- [x] Membuat kelas Export (`NilaiRaportExport.php`) untuk *template* Excel.
- [x] Membuat kelas Import (`NilaiRaportImport.php`) untuk menangani *upload* Excel.
- [x] Membuat `NilaiRaportController.php` dengan fungsi `index`, `show`, `store`, `downloadTemplate`, dan `importExcel`.
- [x] Memodifikasi menu *sidebar* di `AuthenticatedLayout.vue` ("Input Nilai Raport").
- [x] Membuat tampilan `Pages/Guru/NilaiRaport/Index.vue` (Daftar kelas).
- [x] Membuat tampilan `Pages/Guru/NilaiRaport/Input.vue` (Tabel form input dan tombol Import/Export).
- [x] Meng-update *Dashboard* Guru untuk mencerminkan status riil dari kelengkapan `nilai_raports`.
- [ ] Menjalankan migrasi database `php artisan migrate` (Menunggu User).
