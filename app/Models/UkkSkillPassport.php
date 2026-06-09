<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UkkSkillPassport extends Model
{
    use HasFactory;

    protected $table = 'tbl_ukk_skill_passport';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kuk()
    {
        return $this->belongsTo(UkkKuk::class, 'kuk_id');
    }
}
