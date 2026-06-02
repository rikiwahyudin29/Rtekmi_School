<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntrianDownload extends Model
{
    use HasFactory;

    protected $table = 'tbl_antrian_downloads';

    protected $fillable = [
        'siswa_id',
        'tipe',
        'status',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }
}
