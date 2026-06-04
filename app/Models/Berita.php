<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'tbl_berita';
    public $timestamps = true;

    protected $fillable = [
        'slug',
        'judul',
        'isi',
        'gambar',
        'penulis',
        'views',
        'is_published'
    ];
}
