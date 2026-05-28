<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalData extends Model
{
    use HasFactory;

    protected $table = 'soal_data';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = time();
            $model->updated_at = time();
        });

        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }

    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class, 'bank_id', 'id');
    }

    public function opsi()
    {
        return $this->hasMany(SoalOpsi::class, 'soal_id', 'id');
    }

    public function couples()
    {
        return $this->hasMany(SoalDataCouple::class, 'soal_id', 'id');
    }

    public function getQuestionAttribute($value)
    {
        $value = str_replace('{BASEURL}', url('/'), $value ?? '');
        return $this->fixMojibake($value);
    }

    public function getShortentryAttribute($value)
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
