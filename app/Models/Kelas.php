<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'tbl_kelas';

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Get the wali kelas (Guru) that owns the Kelas
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id');
    }

    /**
     * Get the jurusan that owns the Kelas
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }

    /**
     * Get all of the siswa for the Kelas
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id', 'id');
    }
}
