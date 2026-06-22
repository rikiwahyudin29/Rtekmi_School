<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasKumpul extends Model
{
    use HasFactory;

    protected $table = 'tbl_tugas_kumpul';
    protected $guarded = ['id'];
    
    // No updated_at or created_at in this table
    public $timestamps = false;

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
