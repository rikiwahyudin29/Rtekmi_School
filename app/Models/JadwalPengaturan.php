<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPengaturan extends Model
{
    use HasFactory;

    protected $table = 'tbl_jadwal_pengaturan';

    protected $fillable = [
        'id_tahun_ajaran',
        'hari_kerja',
        'max_jp_per_hari',
        'izinkan_jam_ganda'
    ];
}
