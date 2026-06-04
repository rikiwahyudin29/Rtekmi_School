<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'tbl_pendaftar';
    public $timestamps = false; // Karena menggunakan tgl_daftar dengan useCurrent() jika ada, atau handle manual.

    protected $fillable = [
        'no_pendaftaran', 'tgl_daftar', 'nama_lengkap', 'nisn', 'nik', 'jk', 'tempat_lahir',
        'tgl_lahir', 'alamat', 'asal_sekolah', 'no_hp_siswa', 'no_hp_ortu', 'jurusan_minat',
        'status_pendaftaran', 'catatan_admin', 'foto', 'berkas_kk', 'berkas_ijazah', 'agama',
        'alamat_jalan', 'rt_rw', 'desa_kelurahan', 'kecamatan', 'kabupaten', 'provinsi',
        'kode_pos', 'nama_ayah', 'pekerjaan_ayah', 'no_hp_ayah', 'nama_ibu', 'pekerjaan_ibu',
        'no_hp_ibu', 'nama_wali', 'pekerjaan_wali', 'no_hp_wali', 'nilai_rata_rata', 'is_migrated'
    ];

    protected $casts = [
        'tgl_daftar' => 'datetime',
        'tgl_lahir' => 'date',
        'is_migrated' => 'boolean',
    ];
}
