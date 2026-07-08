<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'tbl_user_devices';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'last_ip',
        'latitude',
        'longitude',
        'last_login_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
