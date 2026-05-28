# Rencana Implementasi: Input Nilai Raport Guru

Permintaan bos: "Ganti namanya jadi Nilai Raport, penilaian tunggal (tanpa TP, SAS, STS), langsung input saja, dan wajib mendukung fitur Download Template Excel & Import Excel."

## Rencana Perubahan

### 1. Database & Model
- **Migration & Model `NilaiRaport`**:
  Membuat tabel `nilai_raports` untuk menyimpan penilaian tunggal.
  Kolom: `id`, `anggota_rombel_id` (relasi ke siswa di kelas tersebut), `pembelajaran_id` (relasi ke mapel yang diajarkan), `nilai` (angka bulat), `deskripsi` (opsional), dan `timestamps`.

### 2. Controller & Backend Logic
- **`App\Http\Controllers\Guru\NilaiRaportController`**:
  - `index()`: Menampilkan daftar Mata Pelajaran yang diampu oleh Guru yang sedang *login* (mengambil data `Pembelajaran`).
  - `show($pembelajaran_id)`: Menampilkan rincian siswa (dari `anggota_rombels` yang sesuai dengan `kelas_id` pembelajaran tersebut) beserta form input nilainya.
  - `store($pembelajaran_id)`: Fungsi untuk menyimpan nilai yang diinputkan secara manual melalui *web form*.
  - `downloadTemplate($pembelajaran_id)`: Men-*generate* dan mengunduh format Excel (menggunakan `Maatwebsite\Excel`). Template akan memuat nama siswa dan kolom untuk mengisi nilai secara luring (*offline*).
  - `importExcel($pembelajaran_id)`: Membaca *file* Excel yang diunggah Guru dan menyimpan barisan nilai tersebut langsung ke *database*.

### 3. Routes (`routes/web.php`)
Menambahkan rute-rute tersebut di dalam grup rute Guru:
```php
Route::get('/nilai-raport', [NilaiRaportController::class, 'index'])->name('nilai-raport.index');
Route::get('/nilai-raport/{pembelajaran}', [NilaiRaportController::class, 'show'])->name('nilai-raport.show');
Route::post('/nilai-raport/{pembelajaran}', [NilaiRaportController::class, 'store'])->name('nilai-raport.store');
Route::get('/nilai-raport/{pembelajaran}/template', [NilaiRaportController::class, 'downloadTemplate'])->name('nilai-raport.template');
Route::post('/nilai-raport/{pembelajaran}/import', [NilaiRaportController::class, 'importExcel'])->name('nilai-raport.import');
```

### 4. Tampilan Frontend (Vue & Inertia)
- **Modifikasi Sidebar di `AuthenticatedLayout.vue`**:
  Mengubah teks "Input Nilai Akademik" menjadi "Input Nilai Raport" dan mengarahkan ke rute `guru.nilai-raport.index`.
- **`Pages/Guru/NilaiRaport/Index.vue`**:
  Halaman utama yang mirip dengan tabel di *dashboard*, menampilkan daftar kelas dan mapel yang diampu, serta tombol "Input Nilai" untuk masing-masing baris.
- **`Pages/Guru/NilaiRaport/Input.vue`**:
  Antarmuka untuk Guru memasukkan nilai secara langsung (*inline input* di tabel web) ATAU menekan tombol aksi "Download Format Excel" dan "Upload Excel".

### 5. Penghubung Indikator Dashboard
Mengubah teks "Belum Lengkap" (*placeholder* di *dashboard* guru) agar secara dinamis mengecek ke tabel `nilai_raports`:
- Jika jumlah siswa di kelas = jumlah siswa yang sudah memiliki nilai di mapel terkait, maka status berubah hijau menjadi **"Sudah Lengkap"**.
- Jika masih ada siswa yang kosong, maka berwarna oranye **"Belum Lengkap"**.

## Verifikasi
- *Login* sebagai Guru.
- Klik menu "Input Nilai Raport".
- Memilih salah satu kelas.
- Mengunduh *template* Excel, mengisinya, dan mencoba melakukan *Import*.
- Mengecek apakah status nilai di Dashboard berubah menjadi "Sudah Lengkap".

> [!NOTE]
> Menunggu konfirmasi dari Bos. Apakah rencana rancangan satu nilai utuh ditambah format Excel ini sudah pas dan siap digas?
