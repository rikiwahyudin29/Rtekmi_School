<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBayar extends Model
{
    use HasFactory;

    protected $table = 'tbl_jenis_bayar';

    protected $fillable = [
        'id_pos_bayar',
        'id_tahun_ajaran',
        'tipe_bayar',
        'nominal_default',
        'is_per_jurusan',
    ];

    public function posBayar()
    {
        return $this->belongsTo(PosBayar::class, 'id_pos_bayar', 'id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'id_jenis_bayar', 'id');
    }

    public function jenisBayarJurusan()
    {
        return $this->hasMany(JenisBayarJurusan::class, 'id_jenis_bayar', 'id');
    }
}
