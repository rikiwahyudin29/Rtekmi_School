<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKerusakan extends Model
{
    use HasFactory;

    protected $table = 'tbl_laporan_kerusakan';
    
    protected $fillable = [
        'inventaris_id',
        'pelapor',
        'deskripsi',
        'tgl_lapor',
        'status',
        'tindakan_perbaikan'
    ];

    public function inventaris()
    {
        return $this->belongsTo(\App\Models\Inventaris::class, 'inventaris_id', 'id');
    }
}
