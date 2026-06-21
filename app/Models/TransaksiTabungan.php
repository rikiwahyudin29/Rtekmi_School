<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiTabungan extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaksi_tabungan';
    const UPDATED_AT = null;
    
    protected $fillable = [
        'rekening_id',
        'jenis_transaksi',
        'nominal',
        'saldo_setelah_transaksi',
        'keterangan',
        'referensi_tujuan',
        'petugas_id'
    ];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'rekening_id', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id', 'id');
    }
}
