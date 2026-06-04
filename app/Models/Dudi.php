<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dudi extends Model
{
    use HasFactory;

    protected $table = 'tbl_dudi';
    public $timestamps = true;

    protected $fillable = [
        'nama_dudi',
        'bidang_usaha',
        'alamat_lengkap',
        'nama_pimpinan',
        'latitude',
        'longitude',
        'radius_absen'
    ];
}
