<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'tbl_surat_keluar';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function template()
    {
        return $this->belongsTo(SuratTemplate::class, 'template_id');
    }

    public function ttdOleh()
    {
        return $this->belongsTo(User::class, 'ttd_oleh');
    }
}
