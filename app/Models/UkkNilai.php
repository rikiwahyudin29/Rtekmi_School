<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UkkNilai extends Model
{
    use HasFactory;
    protected $table = 'tbl_ukk_nilai';
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function paket()
    {
        return $this->belongsTo(UkkPaket::class, 'paket_id');
    }

    public function asesorInternal()
    {
        return $this->belongsTo(Guru::class, 'asesor_internal_id');
    }

    public function asesorEksternal()
    {
        return $this->belongsTo(Asesor::class, 'asesor_eksternal_id');
    }
}
