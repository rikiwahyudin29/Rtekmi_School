<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    use HasFactory;

    protected $table = 'tbl_galeri';
    public $timestamps = false;

    protected $fillable = [
        'judul',
        'gambar',
        'kategori',
        'created_at'
    ];
}
