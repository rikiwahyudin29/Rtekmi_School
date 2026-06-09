<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5Nilai extends Model
{
    use HasFactory;
    protected $table = 'tbl_p5_nilai';
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function subElemen()
    {
        return $this->belongsTo(P5SubElemen::class, 'sub_elemen_id');
    }
}
