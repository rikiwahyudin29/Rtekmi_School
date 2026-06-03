<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'tbl_hari_libur';
    public $timestamps = false;
    protected $fillable = [
        'tanggal',
        'keterangan',
    ];

    /**
     * Mengambil dan cache data Libur Nasional dari API eksternal
     */
    public static function getLiburNasionalMap($tahun)
    {
        return \Illuminate\Support\Facades\Cache::remember('libur_nasional_' . $tahun, 86400 * 30, function () use ($tahun) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://dayoffapi.vercel.app/api?year=' . $tahun);
                if ($response->successful()) {
                    $data = $response->json();
                    $map = [];
                    foreach ($data as $item) {
                        if (isset($item['is_cuti']) && $item['is_cuti'] == false) {
                            $map[$item['tanggal']] = $item['keterangan'];
                        }
                    }
                    return $map;
                }
            } catch (\Exception $e) {
                // Abaikan jika API gagal/timeout
            }
            return [];
        });
    }

    /**
     * Cek apakah suatu tanggal libur (Internal, Nasional, atau Weekend)
     * Return: false jika tidak libur, string keterangan jika libur
     */
    public static function cekIsLibur($tanggal)
    {
        // 1. Cek Weekend
        $kode_hari = date('N', strtotime($tanggal));
        if ($kode_hari == 6 || $kode_hari == 7) {
            return 'Libur Akhir Pekan';
        }

        // 2. Cek Internal (Database)
        $internal = self::where('tanggal', $tanggal)->first();
        if ($internal) {
            return $internal->keterangan;
        }

        // 3. Cek Nasional (API)
        $tahun = date('Y', strtotime($tanggal));
        $nasional = self::getLiburNasionalMap($tahun);
        if (isset($nasional[$tanggal])) {
            return $nasional[$tanggal];
        }

        return false;
    }
}
