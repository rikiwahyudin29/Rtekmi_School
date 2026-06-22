<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPengeluaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_jenis_pengeluaran';
    public $timestamps = false;

    protected $fillable = [
        'nama_jenis',
        'keterangan',
    ];

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_jenis', 'id');
    }
}
