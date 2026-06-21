<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_siswa_pelanggaran';
    public $timestamps = false;
    
    protected $fillable = [
        'siswa_id',
        'pelanggaran_id',
        'pelapor_id',
        'tanggal',
        'catatan',
        'status'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    public function pelanggaran()
    {
        return $this->belongsTo(MasterPelanggaran::class, 'pelanggaran_id', 'id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id', 'id');
    }
}
