<?php

namespace App\Http\Controllers\Admin\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\JamMaster; // Assuming tbl_jam_master for times
use App\Models\PembagianTugas;
use App\Services\TimetableEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf; // Or simple HTML view

class JadwalPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();

        $filterKelas = $request->input('id_kelas');
        $filterGuru = $request->input('id_guru');
        $filterJurusan = $request->input('id_jurusan');

        $query = JadwalPelajaran::with(['kelas.jurusan', 'mapel', 'guru', 'tahunAjaran']);

        if ($tahunAktif) {
            $query->where('id_tahun_ajaran', $tahunAktif->id);
        }

        if ($filterKelas) {
            $query->where('id_kelas', $filterKelas);
        }

        if ($filterGuru) {
            $query->where('id_guru', $filterGuru);
        }

        if ($filterJurusan) {
            $query->whereHas('kelas', function($q) use ($filterJurusan) {
                $q->where('id_jurusan', $filterJurusan);
            });
        }

        // Custom order by days in database (MySQL FIELD)
        $query->orderByRaw('FIELD(hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu")')
              ->orderBy('jam_mulai', 'ASC');

        $jadwal = $query->get();

        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $guru = Guru::all();
        $jurusan = Jurusan::all();
        $jamMaster = JamMaster::all();
        
        $mapping = PembagianTugas::with('guru')->where('id_tahun_ajaran', $tahunAktif ? $tahunAktif->id : 0)->get();

        return Inertia::render('Admin/Kurikulum/JadwalPelajaran/Index', [
            'jadwal' => $jadwal,
            'kelas' => $kelas,
            'mapel' => $mapel,
            'guru' => $guru,
            'jurusan' => $jurusan,
            'tahun_aktif' => $tahunAktif,
            'jam_master' => $jamMaster,
            'mapping' => $mapping,
            'filters' => $request->only('id_kelas', 'id_guru', 'id_jurusan')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tahun_ajaran' => 'required',
            'id_kelas' => 'required',
            'id_mapel' => 'required',
            'id_guru' => 'required',
            'hari' => 'required',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $bentrok = $this->cekBentrok(
            $request->id_guru,
            $request->id_kelas,
            $request->hari,
            $request->jam_mulai,
            $request->jam_selesai
        );

        if ($bentrok !== "AMAN") {
            return back()->with('error', 'GAGAL: ' . $bentrok);
        }

        JadwalPelajaran::create($request->all());

        return back()->with('message', 'Jadwal berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        
        $request->validate([
            'id_tahun_ajaran' => 'required',
            'id_kelas' => 'required',
            'id_mapel' => 'required',
            'id_guru' => 'required',
            'hari' => 'required',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $bentrok = $this->cekBentrok(
            $request->id_guru,
            $request->id_kelas,
            $request->hari,
            $request->jam_mulai,
            $request->jam_selesai,
            $id
        );

        if ($bentrok !== "AMAN") {
            return back()->with('error', 'GAGAL: ' . $bentrok);
        }

        $jadwal->update($request->all());

        return back()->with('message', 'Jadwal berhasil diupdate!');
    }

    public function destroy($id)
    {
        JadwalPelajaran::findOrFail($id)->delete();
        return back()->with('message', 'Jadwal berhasil dihapus.');
    }

    private function cekBentrok($id_guru, $id_kelas, $hari, $jam_mulai, $jam_selesai, $id_jadwal_exclude = null)
    {
        // 1. CEK BENTROK GURU
        $queryGuru = JadwalPelajaran::where('hari', $hari)
            ->where('id_guru', $id_guru)
            ->where(function($q) use ($jam_mulai, $jam_selesai) {
                $q->where('jam_mulai', '<', $jam_selesai)
                  ->where('jam_selesai', '>', $jam_mulai);
            });
        
        if ($id_jadwal_exclude) {
            $queryGuru->where('id', '!=', $id_jadwal_exclude);
        }
        
        if ($queryGuru->exists()) {
            return "GURU TERSEBUT SUDAH ADA JADWAL DI JAM INI!";
        }

        // 2. CEK BENTROK KELAS
        $queryKelas = JadwalPelajaran::where('hari', $hari)
            ->where('id_kelas', $id_kelas)
            ->where(function($q) use ($jam_mulai, $jam_selesai) {
                $q->where('jam_mulai', '<', $jam_selesai)
                  ->where('jam_selesai', '>', $jam_mulai);
            });
            
        if ($id_jadwal_exclude) {
            $queryKelas->where('id', '!=', $id_jadwal_exclude);
        }
        
        if ($queryKelas->exists()) {
            return "KELAS INI SEDANG DIPAKAI PELAJARAN LAIN!";
        }

        return "AMAN";
    }

    public function rekap(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();

        $jadwal = JadwalPelajaran::with(['guru', 'kelas', 'mapel'])
            ->where('id_tahun_ajaran', $tahunAktif ? $tahunAktif->id : 0)
            ->get()
            ->sortBy(function($j) {
                return $j->guru->nama_lengkap ?? '';
            });

        $rekapGuru = [];

        foreach($jadwal as $j) {
            $guruId = $j->id_guru;
            if (!$guruId || !$j->guru) continue;

            $start = strtotime($j->jam_mulai);
            $end = strtotime($j->jam_selesai);
            $diffMenit = ($end - $start) / 60;

            if (!isset($rekapGuru[$guruId])) {
                $rekapGuru[$guruId] = [
                    'id_guru' => $guruId,
                    'nama' => $j->guru->nama_lengkap,
                    'nip' => $j->guru->nip,
                    'total_menit' => 0,
                    'kelas_ajar' => [],
                    'jam_asli' => '',
                    'total_jp_40' => 0,
                    'total_jp_45' => 0
                ];
            }

            $rekapGuru[$guruId]['total_menit'] += $diffMenit;
            $namaKelas = $j->kelas->nama_kelas ?? 'N/A';
            if (!in_array($namaKelas, $rekapGuru[$guruId]['kelas_ajar'])) {
                $rekapGuru[$guruId]['kelas_ajar'][] = $namaKelas;
            }
        }

        foreach($rekapGuru as $key => $val) {
            $rekapGuru[$key]['total_jp_40'] = round($val['total_menit'] / 40, 1);
            $rekapGuru[$key]['total_jp_45'] = round($val['total_menit'] / 45, 1);
            $jam = floor($val['total_menit'] / 60);
            $menit = $val['total_menit'] % 60;
            $rekapGuru[$key]['jam_asli'] = $jam . " Jam " . ($menit > 0 ? $menit . " Menit" : "");
        }

        return Inertia::render('Admin/Kurikulum/JadwalPelajaran/Rekap', [
            'rekap' => array_values($rekapGuru),
            'tahun_aktif' => $tahunAktif
        ]);
    }

    public function cetak(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();
        
        $filterKelas = $request->input('id_kelas');
        $filterGuru  = $request->input('id_guru');
        $filterJurusan = $request->input('id_jurusan');

        $query = JadwalPelajaran::with(['kelas.jurusan', 'mapel', 'guru', 'tahunAjaran']);
        if ($tahunAktif) $query->where('id_tahun_ajaran', $tahunAktif->id);
        if ($filterKelas) $query->where('id_kelas', $filterKelas);
        if ($filterGuru) $query->where('id_guru', $filterGuru);
        if ($filterJurusan) {
            $query->whereHas('kelas', function($q) use ($filterJurusan) {
                $q->where('id_jurusan', $filterJurusan);
            });
        }
        $query->orderByRaw('FIELD(hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu")')
              ->orderBy('jam_mulai', 'ASC');

        $jadwal = $query->get();

        $judul = "Jadwal Pelajaran Sekolah";
        $subJudul = "Semua Data";
        
        if($filterKelas) {
            $k = Kelas::find($filterKelas);
            $judul = "Jadwal Kelas " . ($k->nama_kelas ?? '-');
            $subJudul = "Wali Kelas: " . ($k->wali_kelas ?? '-');
        } elseif($filterGuru) {
            $g = Guru::find($filterGuru);
            $judul = "Jadwal Mengajar Guru";
            $subJudul = $g->nama_lengkap ?? '-';
        } elseif($filterJurusan) {
            $j = Jurusan::find($filterJurusan);
            $judul = "Jadwal Jurusan " . ($j->nama_jurusan ?? '-');
        }

        return view('admin.jadwal.cetak', compact('jadwal', 'tahunAktif', 'judul', 'subJudul'));
    }

    public function cetakRekap(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();

        $jadwal = JadwalPelajaran::with(['guru', 'kelas', 'mapel'])
            ->where('id_tahun_ajaran', $tahunAktif ? $tahunAktif->id : 0)
            ->get()
            ->sortBy(function($j) {
                return $j->guru->nama_lengkap ?? '';
            });

        $rekapGuru = [];
        foreach($jadwal as $j) {
            $guruId = $j->id_guru;
            if (!$guruId || !$j->guru) continue;

            $start = strtotime($j->jam_mulai);
            $end = strtotime($j->jam_selesai);
            $diffMenit = ($end - $start) / 60;

            if (!isset($rekapGuru[$guruId])) {
                $rekapGuru[$guruId] = [
                    'nama' => $j->guru->nama_lengkap,
                    'nip' => $j->guru->nip,
                    'total_menit' => 0,
                    'kelas_ajar' => [],
                ];
            }

            $rekapGuru[$guruId]['total_menit'] += $diffMenit;
            $namaKelas = $j->kelas->nama_kelas ?? 'N/A';
            if (!in_array($namaKelas, $rekapGuru[$guruId]['kelas_ajar'])) {
                $rekapGuru[$guruId]['kelas_ajar'][] = $namaKelas;
            }
        }

        foreach($rekapGuru as $key => $val) {
            $rekapGuru[$key]['total_jp_40'] = round($val['total_menit'] / 40, 1);
            $rekapGuru[$key]['total_jp_45'] = round($val['total_menit'] / 45, 1);
            $jam = floor($val['total_menit'] / 60);
            $menit = $val['total_menit'] % 60;
            $rekapGuru[$key]['jam_asli'] = $jam . " Jam " . ($menit > 0 ? $menit . " Menit" : "");
        }

        return view('admin.jadwal.cetak_rekap', compact('rekapGuru', 'tahunAktif'));
    }

    public function templateExcel()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_jadwal.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Header
            fputcsv($file, ['HARI', 'JAM_KE', 'DURASI_JP', 'NAMA_MAPEL', 'NAMA_KELAS', 'NIP_ATAU_NAMA_GURU']);
            // Contoh Data
            fputcsv($file, ['Senin', '1', '2', 'Matematika', 'X MIPA 1', 'Budi Santoso']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();
        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran yang aktif. Silakan seting terlebih dahulu.');
        }

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");
        $header = true;
        
        // Cache data untuk lookup cepat
        $jamMaster = JamMaster::orderBy('urutan', 'ASC')->get();
        $mapelCache = Mapel::all()->keyBy(function($item) { return strtolower(trim($item->nama_mapel)); });
        $kelasCache = Kelas::all()->keyBy(function($item) { return strtolower(trim($item->nama_kelas)); });
        // Lookup guru bisa by NIP atau Nama
        $guruCacheByNip = Guru::whereNotNull('nip')->get()->keyBy('nip');
        $guruCacheByNama = Guru::all()->keyBy(function($item) { return strtolower(trim($item->nama_lengkap)); });

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $count = 0;
            $errors = [];
            $rowNum = 1;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowNum++;
                // Support semicolon separator if comma fails
                if (count($data) == 1 && strpos($data[0], ';') !== false) {
                    $data = explode(';', $data[0]);
                }

                if ($header) {
                    $header = false;
                    continue;
                }

                if (count($data) < 6 || empty($data[0])) continue;

                $hari = trim($data[0]);
                $jamKe = (int) trim($data[1]);
                $durasiJp = (int) trim($data[2]);
                $namaMapel = strtolower(trim($data[3]));
                $namaKelas = strtolower(trim($data[4]));
                $identitasGuru = trim($data[5]);

                // 1. Cari Mapel
                $mapel = $mapelCache->get($namaMapel);
                if (!$mapel) {
                    $errors[] = "Baris $rowNum: Mapel '{$data[3]}' tidak ditemukan.";
                    continue;
                }

                // 2. Cari Kelas
                $kelas = $kelasCache->get($namaKelas);
                if (!$kelas) {
                    $errors[] = "Baris $rowNum: Kelas '{$data[4]}' tidak ditemukan.";
                    continue;
                }

                // 3. Cari Guru
                $guru = $guruCacheByNip->get($identitasGuru) ?? $guruCacheByNama->get(strtolower($identitasGuru));
                if (!$guru) {
                    $errors[] = "Baris $rowNum: Guru '{$identitasGuru}' tidak ditemukan.";
                    continue;
                }

                // 4. Hitung Jam Mulai & Jam Selesai
                if ($jamKe <= 0 || $durasiJp <= 0) {
                    $errors[] = "Baris $rowNum: Jam Ke / Durasi tidak valid.";
                    continue;
                }

                $jamMulai = null;
                $jamSelesai = null;
                $startIndex = $jamMaster->search(function($item) use ($jamKe) {
                    return $item->urutan == $jamKe;
                });

                if ($startIndex !== false) {
                    $jamMulaiObj = $jamMaster[$startIndex];
                    $jamMulai = substr($jamMulaiObj->jam_mulai, 0, 5);
                    
                    $countJp = 0;
                    $targetJamObj = null;
                    for ($i = $startIndex; $i < $jamMaster->count(); $i++) {
                        if ($jamMaster[$i]->is_istirahat == 0) {
                            $countJp++;
                        }
                        if ($countJp === $durasiJp) {
                            $targetJamObj = $jamMaster[$i];
                            break;
                        }
                    }
                    
                    if ($targetJamObj) {
                        $jamSelesai = substr($targetJamObj->jam_selesai, 0, 5);
                    } else {
                        // Jika kelewatan (durasi kepanjangan), ambil jam selesai paling akhir
                        $jamSelesai = substr($jamMaster->last()->jam_selesai, 0, 5);
                    }
                }

                if (!$jamMulai || !$jamSelesai) {
                    $errors[] = "Baris $rowNum: Waktu untuk Jam Ke-$jamKe tidak ditemukan di Master Jam.";
                    continue;
                }

                // Validasi bentrok
                $bentrok = $this->cekBentrok($guru->id, $kelas->id, $hari, $jamMulai, $jamSelesai);
                if ($bentrok !== "AMAN") {
                    $errors[] = "Baris $rowNum gagal: " . $bentrok;
                    continue;
                }

                JadwalPelajaran::create([
                    'id_tahun_ajaran' => $tahunAktif->id,
                    'hari' => $hari,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'id_mapel' => $mapel->id,
                    'id_kelas' => $kelas->id,
                    'id_guru' => $guru->id,
                ]);

                $count++;
            }
            fclose($handle);
            \Illuminate\Support\Facades\DB::commit();

            if (count($errors) > 0) {
                return redirect()->route('admin.kurikulum.jadwal-pelajaran.index')
                    ->with('message', "Berhasil mengimport {$count} data jadwal.")
                    ->with('error', "Beberapa data gagal diimport: " . implode(', ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? " ...dan " . (count($errors)-5) . " lainnya." : ""));
            }

            return redirect()->route('admin.kurikulum.jadwal-pelajaran.index')
                ->with('message', "Berhasil mengimport {$count} data jadwal baru.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function autoGenerate(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();
        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $engine = new TimetableEngine($tahunAktif->id);
        $result = $engine->autoGenerate();

        if ($result['status'] === 'success') {
            return back()->with('message', $result['msg']);
        } else {
            return back()->with('error', 'Gagal Generate: ' . $result['msg']);
        }
    }
}
