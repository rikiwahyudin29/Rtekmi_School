<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_pengeluaran';

    // The table only has created_at, no updated_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'id_divisi',
        'id_jenis',
        'judul_pengeluaran',
        'nominal',
        'tanggal',
        'keterangan',
        'petugas_id',
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisPengeluaran::class, 'id_jenis', 'id');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id', 'id');
    }
}
