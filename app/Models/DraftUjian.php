<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftUjian extends Model
{
    protected $table = 'draft_ujian';
    
    // As seen in migration, there are no created_at/updated_at by default
    public $timestamps = false;

    protected $guarded = [];

    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function soals()
    {
        return $this->belongsToMany(SoalData::class, 'draft_soal', 'draft_id', 'soal_id');
    }
}
