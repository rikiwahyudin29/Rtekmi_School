# Modul Input Nilai Raport Tunggal

Sistem penilaian kini telah dirombak menjadi lebih efisien dengan penerapan **Penilaian Tunggal** per mata pelajaran, memangkas kompleksitas seperti TP, SAS, dan STS. 

## Fungsionalitas yang Ditambahkan

### 1. Struktur Database Baru
- Tabel **`nilai_raports`** telah dibuat untuk menampung satu nilai utuh dari setiap siswa berdasarkan mata pelajaran yang diikutinya di semester aktif. 
- Penamaan relasi antar *database* (*Siswa -> Rombel -> Kelas -> Mapel -> Guru*) telah disusun secara kokoh.

### 2. Antarmuka Baru yang Modern
- Di *sidebar* Guru, label kini telah berubah elegan menjadi **Input Nilai Raport**.
- Tersedia halaman khusus `guru/nilai-raport` yang menampilkan daftar Mata Pelajaran & Kelas yang diampu lengkap dengan **Status Pengisian Nilai** (Sudah Lengkap / Belum Lengkap). Status ini terhubung *real-time* ke *database*; jika masih ada siswa yang belum diberi nilai, statusnya otomatis menjadi "Belum Lengkap".

### 3. Halaman Input & Sinkronisasi Excel
- Guru bisa mengeklik **"Input Nilai"** pada kelas manapun dan dihadapkan pada tabel nama-nama siswa yang elegan.
- Guru dapat mengisi nilai satu-persatu di layar secara langsung (*Inline Input*).
- **Fitur Andalan**: Terdapat tombol **Download Template** (menghasilkan *file* Excel berformat `.xlsx` yang sudah terisi otomatis nama dan identitas siswa) serta tombol **Upload Nilai Excel** untuk mengunggah kembali nilai yang sudah diisi secara luring (*offline*).

### 4. Integrasi ke Dashboard
Dashboard utama Guru sekarang membaca data dari sistem nilai yang riil ini, memastikan angka "Mulai Mengisi Nilai" dan "Status" yang tampil benar-benar mencerminkan kondisi sebenarnya dari pengerjaan sang guru.

---

> [!WARNING]
> Fitur ini belum dapat dicoba hingga Bos menjalankan perintah `php artisan migrate` di terminal, mengingat sistem perlu menanamkan kerangka tabel nilai yang baru ke dalam pusat datanya.
