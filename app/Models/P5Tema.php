<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5Tema extends Model
{
    use HasFactory;
    protected $table = 'tbl_p5_tema';
    protected $guarded = [];

    public function projek()
    {
        return $this->hasMany(P5Projek::class, 'tema_id');
    }
}
