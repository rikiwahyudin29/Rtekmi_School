<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konseling extends Model
{
    use HasFactory;

    protected $table = 'tbl_konseling';
    
    protected $fillable = [
        'siswa_id',
        'guru_id',
        'tanggal_konseling',
        'jenis_konseling',
        'topik',
        'hasil_konseling',
        'tindak_lanjut',
        'status'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id', 'id');
    }
}
