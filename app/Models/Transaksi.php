<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaksi';

    protected $fillable = [
        'kode_transaksi',
        'merchant_ref',
        'reference',
        'id_tagihan',
        'id_siswa',
        'jumlah_bayar',
        'fee_admin',
        'total_bayar',
        'status_transaksi',
        'checkout_url',
        'payment_type',
        'tanggal_bayar',
        'petugas_id',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan', 'id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id', 'id');
    }
}
