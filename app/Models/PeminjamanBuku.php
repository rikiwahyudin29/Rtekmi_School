<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanBuku extends Model
{
    use HasFactory;

    protected $table = 'tbl_peminjaman_buku';
    public $timestamps = false;
    
    protected $fillable = [
        'id_siswa',
        'id_buku',
        'tgl_pinjam',
        'tgl_kembali_seharusnya',
        'tgl_dikembalikan',
        'status',
        'denda'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'id_buku', 'id');
    }
}
