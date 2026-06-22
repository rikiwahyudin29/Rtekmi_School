<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tbl_tugas';
    protected $guarded = ['id'];
    
    // Non-standard timestamps in this table, no updated_at
    public $timestamps = false;

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function kumpul()
    {
        return $this->hasMany(TugasKumpul::class, 'tugas_id');
    }
}
