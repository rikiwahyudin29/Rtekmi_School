<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalOpsi extends Model
{
    use HasFactory;

    protected $table = 'soal_opsi';
    protected $guarded = ['id'];
    public $timestamps = false; // Karena di migration tidak ada created_at dan updated_at

    public function soal()
    {
        return $this->belongsTo(SoalData::class, 'soal_id', 'id');
    }

    public function couple()
    {
        return $this->belongsTo(SoalDataCouple::class, 'soal_couple_id', 'id');
    }

    public function getBodyAttribute($value)
    {
        $value = str_replace('{BASEURL}', url('/'), $value ?? '');
        return $this->fixMojibake($value);
    }

    private function fixMojibake($value)
    {
        if (empty($value)) return $value;
        if (preg_match('/[ØÙÚâ]/u', $value)) {
            $converted = @iconv('UTF-8', 'Windows-1252//IGNORE', $value);
            if ($converted !== false && $converted !== $value && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
            $converted = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $value);
            if ($converted !== false && $converted !== $value && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }
        return $value;
    }
}
