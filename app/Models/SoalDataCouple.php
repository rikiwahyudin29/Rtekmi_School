<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalDataCouple extends Model
{
    use HasFactory;

    protected $table = 'soal_data_couple';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function soal()
    {
        return $this->belongsTo(SoalData::class, 'soal_id', 'id');
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
