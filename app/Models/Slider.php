<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'tbl_slider';
    public $timestamps = false;

    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'urutan',
        'is_active',
        'created_at'
    ];
}
