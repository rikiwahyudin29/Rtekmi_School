<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'm_banksoal';
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

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function soalData()
    {
        return $this->hasMany(SoalData::class, 'bank_id', 'id');
    }
}
