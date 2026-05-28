<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'tbl_siswa';

    protected $guarded = ['id'];

    /**
     * Get the kelas that owns the Siswa
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }

    /**
     * Get the jurusan that owns the Siswa
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }

    /**
     * Get the ekskul that owns the Siswa
     */
    public function ekskul(): BelongsTo
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id', 'id');
    }

    /**
     * Get the user associated with the Siswa
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kelulusan()
    {
        return $this->hasOne(Kelulusan::class, 'siswa_id');
    }

    public function nilaiSiswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'siswa_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'user_id')->where('role', 'siswa');
    }
}
