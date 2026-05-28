<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DapodikSetting extends Model
{
    use HasFactory;

    protected $table = 'tbl_dapodik_setting';
    protected $guarded = ['id'];
    public $timestamps = false;
}
