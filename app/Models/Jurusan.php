<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'tbl_jurusan';

    protected $guarded = ['id'];
    public $timestamps = false;

    /**
     * Get the guru that owns the Jurusan (Kepala Jurusan)
     */
    public function kepalaJurusan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Guru::class, 'kepala_jurusan_id', 'id');
    }
}
