<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruKetersediaan extends Model
{
    use HasFactory;

    protected $table = 'tbl_guru_ketersediaan';

    protected $fillable = [
        'id_guru',
        'id_tahun_ajaran',
        'hari',
        'id_jam_master',
        'is_available'
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id');
    }

    public function jamMaster()
    {
        return $this->belongsTo(JamMaster::class, 'id_jam_master', 'id');
    }
}
