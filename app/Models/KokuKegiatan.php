<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KokuKegiatan extends Model
{
    use HasFactory;

    protected $table = 'tbl_koku_kegiatan';
    protected $guarded = ['id'];

    public function tema()
    {
        return $this->belongsTo(KokuTema::class, 'tema_id');
    }
}
