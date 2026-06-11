<?php

namespace App\Services;

use App\Models\PembagianTugas;
use App\Models\JadwalPelajaran;
use App\Models\JamMaster;
use App\Models\JadwalPengaturan;
use App\Models\GuruKetersediaan;
use Illuminate\Support\Facades\DB;

class TimetableEngine
{
    protected $idTahunAjaran;
    protected $pengaturan;
    protected $jamMasters;
    protected $guruTidakTersedia = [];
    protected $jadwalMemory = [];
    protected $memoryGuru = [];
    protected $memoryKelas = [];
    
    // Default hari kerja
    protected $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function __construct($idTahunAjaran)
    {
        $this->idTahunAjaran = $idTahunAjaran;
        $this->pengaturan = JadwalPengaturan::where('id_tahun_ajaran', $idTahunAjaran)->first();
        
        // Load jam pelajaran (abaikan waktu istirahat)
        $this->jamMasters = JamMaster::where('is_istirahat', 0)->orderBy('urutan', 'ASC')->get();
        
        // Potong daftar hari jika sekolah cuma 5 hari
        if ($this->pengaturan && $this->pengaturan->hari_kerja == 5) {
            $this->daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        }
    }

    /**
     * Memuat constraints ketersediaan guru ke dalam memory agar cepat diakses
     */
    protected function loadConstraints()
    {
        $ketersediaan = GuruKetersediaan::where('id_tahun_ajaran', $this->idTahunAjaran)->get();
        foreach ($ketersediaan as $k) {
            if (!$k->is_available) {
                // Key format: guruId_hari_jamMasterId
                $key = $k->id_guru . '_' . $k->hari . '_' . $k->id_jam_master;
                $this->guruTidakTersedia[$key] = true;
            }
        }
    }

    /**
     * Mengecek apakah guru mengajar di jam lain pada hari dan slot yang sama
     */
    public function checkKres($idGuru, $hari, $idJamMaster)
    {
        if ($this->pengaturan && $this->pengaturan->izinkan_jam_ganda) {
            return false;
        }

        // Cek di memory (untuk auto generator) O(1)
        $key = $idGuru . '_' . $hari . '_' . $idJamMaster;
        if (isset($this->memoryGuru[$key])) {
            return true; // Kres!
        }

        // Cek di Database (untuk input manual via UI)
        $kres = JadwalPelajaran::where('id_tahun_ajaran', $this->idTahunAjaran)
            ->where('id_guru', $idGuru)
            ->where('hari', $hari)
            ->where('id_jam_master', $idJamMaster)
            ->exists();

        return $kres;
    }

    /**
     * Algoritma Auto Generate Jadwal menggunakan heuristik sederhana
     */
    public function autoGenerate()
    {
        DB::beginTransaction();
        try {
            // 1. Kosongkan jadwal yang ada di tahun ajaran ini
            JadwalPelajaran::where('id_tahun_ajaran', $this->idTahunAjaran)->delete();
            
            // 2. Load constraints
            $this->loadConstraints();
            $this->jadwalMemory = [];
            $this->memoryGuru = [];
            $this->memoryKelas = [];

            // 3. Ambil semua beban mengajar (urutkan dari beban terbesar agar lebih mudah dipasang di awal)
            $bebanTugas = PembagianTugas::where('id_tahun_ajaran', $this->idTahunAjaran)
                                ->orderBy('beban_jam', 'DESC')
                                ->get();

            $gagalGenerate = 0;

            // 4. Proses pendistribusian jadwal
            foreach ($bebanTugas as $tugas) {
                $sisaJam = $tugas->beban_jam;
                
                // Jika tidak ada beban jam, lewati
                if ($sisaJam <= 0) continue;

                // Coba pecah jam menjadi blok 2 JP (standar umum)
                while ($sisaJam > 0) {
                    $blokJam = min(2, $sisaJam); // Ambil 2 jam, atau 1 jam jika sisa 1
                    
                    // Cari slot kosong berturut-turut untuk kelas dan guru ini
                    $ditemukan = false;
                    
                    // Acak hari agar distribusi merata
                    $acakHari = $this->daftarHari;
                    shuffle($acakHari);

                    foreach ($acakHari as $hari) {
                        for ($i = 0; $i <= count($this->jamMasters) - $blokJam; $i++) {
                            
                            $slotBisaDipakai = true;
                            $tempSlots = [];

                            for ($j = 0; $j < $blokJam; $j++) {
                                $jamMaster = $this->jamMasters[$i + $j];

                                // a. Cek apakah slot ini diblokir manual oleh guru (is_available = false)
                                $constraintKey = $tugas->id_guru . '_' . $hari . '_' . $jamMaster->id;
                                if (isset($this->guruTidakTersedia[$constraintKey])) {
                                    $slotBisaDipakai = false;
                                    break;
                                }

                                // b. Cek apakah Kelas sedang belajar mapel lain di jam ini (O(1) lookup)
                                $kelasKey = $tugas->id_kelas . '_' . $hari . '_' . $jamMaster->id;
                                if (isset($this->memoryKelas[$kelasKey])) {
                                    $slotBisaDipakai = false;
                                    break;
                                }

                                // c. Cek apakah Guru sedang mengajar di kelas lain (Kres)
                                if ($this->checkKres($tugas->id_guru, $hari, $jamMaster->id)) {
                                    $slotBisaDipakai = false;
                                    break;
                                }

                                $tempSlots[] = $jamMaster;
                            }

                            if ($slotBisaDipakai) {
                                // YAY! Slot ditemukan, masukkan ke memori
                                foreach ($tempSlots as $ts) {
                                    $this->jadwalMemory[] = [
                                        'id_tahun_ajaran' => $this->idTahunAjaran,
                                        'id_pembagian_tugas' => $tugas->id,
                                        'id_kelas' => $tugas->id_kelas,
                                        'id_mapel' => $tugas->id_mapel,
                                        'id_guru' => $tugas->id_guru,
                                        'hari' => $hari,
                                        'id_jam_master' => $ts->id,
                                        'jam_mulai' => $ts->jam_mulai,
                                        'jam_selesai' => $ts->jam_selesai,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                    
                                    // Daftarkan ke fast-lookup dictionary
                                    $guruKey = $tugas->id_guru . '_' . $hari . '_' . $ts->id;
                                    $kelasKey = $tugas->id_kelas . '_' . $hari . '_' . $ts->id;
                                    $this->memoryGuru[$guruKey] = true;
                                    $this->memoryKelas[$kelasKey] = true;
                                }
                                $sisaJam -= $blokJam;
                                $ditemukan = true;
                                break; // Break dari loop jamMaster
                            }
                        }
                        if ($ditemukan) break; // Break dari loop hari
                    }

                    if (!$ditemukan) {
                        // Mentok! Susah dipasang (bisa jadi kapasitas habis atau full kres)
                        // Turunkan blok jam jadi 1 (jika tadi 2)
                        if ($blokJam == 2) {
                            // Coba loop lagi dengan blok 1
                            continue; 
                        } else {
                            $gagalGenerate += $sisaJam;
                            break; // Stop untuk mapel ini
                        }
                    }
                }
            }

            // 5. Simpan semua memori ke database (Bulk Insert agar cepat)
            // Chunking per 500 records
            $chunks = array_chunk($this->jadwalMemory, 500);
            foreach ($chunks as $chunk) {
                JadwalPelajaran::insert($chunk);
            }

            DB::commit();

            return [
                'status' => 'success',
                'total_terjadwal' => count($this->jadwalMemory),
                'gagal_terjadwal' => $gagalGenerate,
                'msg' => $gagalGenerate > 0 ? "Berhasil, namun ada $gagalGenerate JP yang gagal terpasang karena kelas/guru terlalu padat (kres)." : "Jadwal berhasil digenerate 100% tanpa kres!"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
        }
    }
}
