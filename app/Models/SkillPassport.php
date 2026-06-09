<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillPassport extends Model
{
    use HasFactory;
    protected $table = 'tbl_skill_passport';
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function skkni()
    {
        return $this->belongsTo(Skkni::class, 'skkni_id');
    }
}
