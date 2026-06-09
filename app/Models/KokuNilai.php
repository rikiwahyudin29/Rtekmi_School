<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KokuNilai extends Model
{
    use HasFactory;

    protected $table = 'tbl_koku_nilai';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kegiatan()
    {
        return $this->belongsTo(KokuKegiatan::class, 'kegiatan_id');
    }
}
