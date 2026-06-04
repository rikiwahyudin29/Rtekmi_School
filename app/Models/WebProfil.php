<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebProfil extends Model
{
    use HasFactory;

    protected $table = 'tbl_web_profil';
    public $timestamps = false; // Only has updated_at in migration, handle carefully

    protected $fillable = [
        'deskripsi_hero',
        'nama_kepsek',
        'sambutan_kepsek',
        'foto_kepsek',
        'link_fb',
        'link_ig',
        'link_yt',
        'link_map',
        'spot_hero_png', // We will add this column
        'spot_ppdb_png', // We will add this column
        'updated_at'
    ];
}
