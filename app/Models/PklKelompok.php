<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklKelompok extends Model
{
    use HasFactory;

    protected $table = 'tbl_pkl_kelompok';
    protected $guarded = ['id'];

    public function dudi()
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function tujuanPembelajaran()
    {
        return $this->hasMany(PklTp::class, 'kelompok_id');
    }
}
