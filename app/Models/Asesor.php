<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesor extends Model
{
    use HasFactory;
    protected $table = 'tbl_asesor';
    protected $guarded = [];

    public function dudi()
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }
}
