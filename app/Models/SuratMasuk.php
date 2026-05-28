<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'tbl_surat_masuk';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function disposisi()
    {
        return $this->hasMany(Disposisi::class, 'id_surat');
    }
}
