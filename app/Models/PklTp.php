<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklTp extends Model
{
    use HasFactory;

    protected $table = 'tbl_pkl_tp';
    protected $guarded = ['id'];

    public function kelompok()
    {
        return $this->belongsTo(PklKelompok::class, 'kelompok_id');
    }
}
