<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiFormatif extends Model
{
    use HasFactory;

    protected $table = 'tbl_nilai_formatif';
    protected $guarded = ['id'];

    public function tp()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tp_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
