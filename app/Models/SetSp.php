<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetSp extends Model
{
    use HasFactory;

    protected $table = 'tbl_set_sp';
    public $timestamps = false;
    protected $guarded = ['id'];
}
