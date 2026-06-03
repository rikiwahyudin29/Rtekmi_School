<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBayarJurusan extends Model
{
    use HasFactory;

    protected $table = 'tbl_jenis_bayar_jurusan';

    protected $fillable = [
        'id_jenis_bayar',
        'id_jurusan',
        'nominal',
    ];

    public function jenisBayar()
    {
        return $this->belongsTo(JenisBayar::class, 'id_jenis_bayar', 'id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }
}
