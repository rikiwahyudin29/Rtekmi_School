<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisUjian extends Model
{
    use HasFactory;

    protected $table = 'tbl_jenis_ujian';
    public $timestamps = false;

    protected $fillable = [
        'nama_jenis',
        'kode_jenis',
        'status'
    ];
}
