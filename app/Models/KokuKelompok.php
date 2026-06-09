<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KokuKelompok extends Model
{
    use HasFactory;

    protected $table = 'tbl_koku_kelompok';
    protected $guarded = ['id'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}
