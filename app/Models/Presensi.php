<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'tbl_presensi';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'user_id')->where('role', 'siswa');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'user_id')->where('role', 'guru');
    }
}
