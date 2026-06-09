<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5Kelompok extends Model
{
    use HasFactory;
    protected $table = 'tbl_p5_kelompok';
    protected $guarded = [];

    public function projek()
    {
        return $this->belongsTo(P5Projek::class, 'projek_id');
    }

    public function koordinator()
    {
        return $this->belongsTo(Guru::class, 'guru_koordinator_id');
    }
}
