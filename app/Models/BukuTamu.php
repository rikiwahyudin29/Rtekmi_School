<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuTamu extends Model
{
    use HasFactory;

    protected $table = 'tbl_buku_tamu';
    const UPDATED_AT = null;

    protected $fillable = [
        'tanggal',
        'no_antrian',
        'nama_lengkap',
        'instansi_asal',
        'no_hp',
        'keperluan',
        'kategori',
        'status',
        'ttd',
        'ttd_piket',
        'dikonfirmasi_oleh',
        'bertemu_dengan',
        'jam_datang',
        'jam_pulang',
        'pencatat_id',
    ];
}
