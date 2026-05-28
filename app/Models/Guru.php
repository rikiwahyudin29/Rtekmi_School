<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'tbl_guru';

    protected $guarded = ['id'];

    /**
     * Get the user associated with the Guru
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all of the kelas for the Guru (sebagai wali kelas)
     */
    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'guru_id', 'id');
    }

    /**
     * Get all of the ekskul for the Guru (sebagai pembina)
     */
    public function ekskulBinaan(): HasMany
    {
        return $this->hasMany(Ekskul::class, 'guru_id', 'id');
    }
}
