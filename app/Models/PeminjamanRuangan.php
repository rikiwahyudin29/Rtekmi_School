<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanRuangan extends Model
{
    use HasFactory;

    protected $table = 'tbl_peminjaman_ruangan';
    
    protected $fillable = [
        'ruangan_id',
        'peminjam',
        'kegiatan',
        'tgl_pinjam',
        'tgl_kembali',
        'status',
        'catatan'
    ];

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id', 'id');
    }
}
