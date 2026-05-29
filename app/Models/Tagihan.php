<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tbl_tagihan';

    protected $fillable = [
        'id_jenis_bayar',
        'id_siswa',
        'id_kelas',
        'nominal_tagihan',
        'nominal_terbayar',
        'status_bayar',
        'keterangan',
        'bulan_ke',
    ];

    public function jenisBayar()
    {
        return $this->belongsTo(JenisBayar::class, 'id_jenis_bayar', 'id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_tagihan', 'id');
    }
}
