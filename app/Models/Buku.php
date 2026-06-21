<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'tbl_buku';
    public $timestamps = false; // Migration CI4 for tbl_buku might not have created_at updated_at. I will set false to be safe.
    
    protected $fillable = [
        'kode_buku',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'stok_total',
        'stok_tersedia'
    ];
}
