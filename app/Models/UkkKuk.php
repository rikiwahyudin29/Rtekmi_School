<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UkkKuk extends Model
{
    use HasFactory;

    protected $table = 'tbl_ukk_kuk';
    protected $guarded = ['id'];

    public function skkni()
    {
        return $this->belongsTo(Skkni::class, 'skkni_id');
    }
}
