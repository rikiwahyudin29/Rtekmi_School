<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $table = 'tbl_rekening';
    
    protected $fillable = [
        'siswa_id',
        'pin',
        'saldo',
        'status_rekening'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    public function mutasi()
    {
        return $this->hasMany(TransaksiTabungan::class, 'rekening_id', 'id');
    }
}
