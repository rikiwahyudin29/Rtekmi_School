<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosBayar extends Model
{
    use HasFactory;

    protected $table = 'tbl_pos_bayar';

    protected $fillable = [
        'nama_pos',
        'keterangan',
    ];

    public function jenisBayars()
    {
        return $this->hasMany(JenisBayar::class, 'id_pos_bayar', 'id');
    }
}
