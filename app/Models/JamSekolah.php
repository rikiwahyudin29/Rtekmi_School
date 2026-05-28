<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamSekolah extends Model
{
    use HasFactory;

    protected $table = 'tbl_jam_sekolah';
    protected $guarded = ['id'];
    public $timestamps = false; // We have updated_at but let's handle it manually if needed, or set false because there is no created_at
}
