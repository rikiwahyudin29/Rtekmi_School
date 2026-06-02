<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'tbl_jadwal';

    protected $fillable = [
        'id_pembagian_tugas',
        'id_tahun_ajaran',
        'id_kelas',
        'id_mapel',
        'id_guru',
        'hari',
        'id_jam_master',
        'jam_mulai',
        'jam_selesai',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru');
    }

    public function pembagianTugas()
    {
        return $this->belongsTo(PembagianTugas::class, 'id_pembagian_tugas');
    }

    public function jamMaster()
    {
        return $this->belongsTo(JamMaster::class, 'id_jam_master');
    }
}
