<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JadwalUjian extends Model
{
    use HasFactory;

    protected $table = 'tbl_jadwal_ujian';
    protected $guarded = ['id'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // Karena di migration hanya ada created_at

    public function draftUjian()
    {
        return $this->belongsTo(DraftUjian::class, 'id_bank_soal', 'id');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'tbl_jadwal_kelas', 'id_jadwal_ujian', 'id_kelas');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id');
    }

    public function jenisUjian()
    {
        return $this->belongsTo(JenisUjian::class, 'id_jenis_ujian', 'id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id');
    }

    public function ujianSiswa()
    {
        return $this->hasMany(UjianSiswa::class, 'jadwal_id', 'id');
    }

    /**
     * Auto generate token jika setting_token = 1 dan token masih kosong
     */
    public function generateToken()
    {
        if ($this->setting_token && empty($this->token)) {
            $newToken = strtoupper(Str::random(6));
            $this->update(['token' => $newToken]);
            return $newToken;
        }
        return $this->token;
    }
}
