<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ekskul extends Model
{
    use HasFactory;

    protected $table = 'tbl_ekskul';

    protected $guarded = ['id'];

    public $timestamps = false; // CI4 default table ekskul might not have timestamps

    /**
     * Get the guru (pembina) that owns the Ekskul
     */
    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id');
    }

    /**
     * Get all of the anggota (siswa) for the Ekskul
     */
    public function anggota(): HasMany
    {
        return $this->hasMany(Siswa::class, 'ekskul_id', 'id');
    }
}
