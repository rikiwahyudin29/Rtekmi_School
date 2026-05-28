<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianSiswa extends Model
{
    use HasFactory;

    protected $table = 'ujian_siswa';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function jadwal()
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_id', 'id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }
}
