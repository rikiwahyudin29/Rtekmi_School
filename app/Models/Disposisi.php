<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'tbl_disposisi';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'id_surat');
    }

    public function penerima()
    {
        return $this->belongsTo(Guru::class, 'id_penerima');
    }
}
