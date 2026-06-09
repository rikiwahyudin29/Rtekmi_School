<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KokuTema extends Model
{
    use HasFactory;

    protected $table = 'tbl_koku_tema';
    protected $guarded = ['id'];

    public function kegiatan()
    {
        return $this->hasMany(KokuKegiatan::class, 'tema_id');
    }
}
