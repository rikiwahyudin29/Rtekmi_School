<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5Projek extends Model
{
    use HasFactory;
    protected $table = 'tbl_p5_projek';
    protected $guarded = [];

    public function tema()
    {
        return $this->belongsTo(P5Tema::class, 'tema_id');
    }
}
