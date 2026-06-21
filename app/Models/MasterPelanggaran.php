<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_master_pelanggaran';
    public $timestamps = false;
    
    protected $fillable = [
        'nama_pelanggaran',
        'kategori',
        'poin'
    ];
}
