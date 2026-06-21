<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    use HasFactory;

    protected $table = 'tbl_inventaris';
    
    public $timestamps = false;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'lokasi',
        'jumlah',
        'kondisi',
        'tgl_masuk',
        'keterangan'
    ];
}
