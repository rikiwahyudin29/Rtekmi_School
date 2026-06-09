<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\UkkPaket;
use App\Models\Asesor;
use App\Models\Jurusan;
use App\Models\Skkni;
use App\Models\Dudi;
use App\Models\UkkNilai;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\UkkKuk;
use App\Models\UkkSkillPassport;

class UkkController extends Controller
{
    public function index()
    {
        $skkni = Skkni::with('jurusan')->get();
        $paket = UkkPaket::with('jurusan')->get();
        $asesor = Asesor::with('dudi')->get();
        
        $jurusan = Jurusan::all();
        $dudi = Dudi::all();

        return Inertia::render('Admin/Ukk/Index', [
            'skkni' => $skkni,
            'paket' => $paket,
            'asesor' => $asesor,
            'jurusan' => $jurusan,
            'dudi' => $dudi
        ]);
    }

    public function storeSkkni(Request $request)
    {
        $request->validate([
            'kode_unit' => 'required|string',
            'judul_unit' => 'required|string',
            'jurusan_id' => 'required|integer',
        ]);

        Skkni::create($request->all());

        return redirect()->back()->with('success', 'SKKNI berhasil ditambahkan');
    }

    public function storeAsesor(Request $request)
    {
        $request->validate([
            'nama_asesor' => 'required|string',
            'no_sertifikat' => 'required|string',
            'dudi_id' => 'required|integer',
        ]);

        Asesor::create($request->all());

        return redirect()->back()->with('success', 'Asesor berhasil ditambahkan');
    }

    public function nilai()
    {
        $paket = UkkPaket::with('jurusan')->get();
        $asesor = Asesor::all();
        $guru = Guru::all();
        
        $selected_paket_id = request('paket_id', $paket->first()->id ?? null);
        
        $siswa = [];
        $nilai_existing = [];
        
        if ($selected_paket_id) {
            $paket_selected = UkkPaket::find($selected_paket_id);
            if ($paket_selected) {
                // Cari siswa berdasarkan jurusan paket
                $siswa = Siswa::whereHas('kelas', function($q) use ($paket_selected) {
                    $q->where('jurusan_id', $paket_selected->jurusan_id);
                })->get();
                
                $nilai_existing = UkkNilai::where('paket_id', $selected_paket_id)->get()->keyBy('siswa_id');
            }
        }

        return Inertia::render('Admin/Ukk/Nilai', [
            'paket' => $paket,
            'asesor' => $asesor,
            'guru' => $guru,
            'siswa' => $siswa,
            'nilai_existing' => $nilai_existing,
            'selected_paket_id' => $selected_paket_id
        ]);
    }

    public function storeNilai(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|integer',
            'data' => 'required|array'
        ]);

        foreach ($request->data as $siswa_id => $n) {
            if (isset($n['nilai_pengetahuan']) && isset($n['nilai_keterampilan'])) {
                UkkNilai::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'paket_id' => $request->paket_id
                    ],
                    [
                        'asesor_internal_id' => $n['asesor_internal_id'] ?? null,
                        'asesor_eksternal_id' => $n['asesor_eksternal_id'] ?? null,
                        'nilai_pengetahuan' => $n['nilai_pengetahuan'],
                        'nilai_keterampilan' => $n['nilai_keterampilan'],
                        'kesimpulan' => $n['kesimpulan'] ?? 'Belum Kompeten'
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Nilai UKK berhasil disimpan');
    }

    public function kuk()
    {
        $skkni = Skkni::with('jurusan')->get();
        $kuk = UkkKuk::with('skkni.jurusan')->get();
        
        return Inertia::render('Admin/Ukk/Kuk', [
            'skkni' => $skkni,
            'kuk' => $kuk
        ]);
    }

    public function storeKuk(Request $request)
    {
        $request->validate([
            'skkni_id' => 'required|integer',
            'kode_kuk' => 'required|string',
            'deskripsi_kuk' => 'required|string'
        ]);

        UkkKuk::create($request->all());

        return redirect()->back()->with('success', 'KUK berhasil ditambahkan');
    }

    public function skillPassport()
    {
        $paket = UkkPaket::with('jurusan')->get();
        $selected_paket_id = request('paket_id', $paket->first()->id ?? null);
        
        $siswa = [];
        $skkni = [];
        $skill_passport = [];
        
        if ($selected_paket_id) {
            $paket_selected = UkkPaket::find($selected_paket_id);
            if ($paket_selected) {
                $siswa = Siswa::whereHas('kelas', function($q) use ($paket_selected) {
                    $q->where('jurusan_id', $paket_selected->jurusan_id);
                })->get();
                
                // Get KUK from SKKNI that matches the Jurusan
                $skkni = Skkni::with('kuk')->where('jurusan_id', $paket_selected->jurusan_id)->get();
                
                $skill_passport = UkkSkillPassport::whereIn('siswa_id', $siswa->pluck('id'))->get()->groupBy('siswa_id');
            }
        }

        return Inertia::render('Admin/Ukk/SkillPassport', [
            'paket' => $paket,
            'siswa' => $siswa,
            'skkni' => $skkni,
            'skill_passport' => $skill_passport,
            'selected_paket_id' => $selected_paket_id
        ]);
    }

    public function storeSkillPassport(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        foreach ($request->data as $siswa_id => $kuks) {
            foreach ($kuks as $kuk_id => $status) {
                UkkSkillPassport::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'kuk_id' => $kuk_id
                    ],
                    [
                        'status' => $status
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Skill Passport berhasil disimpan');
    }
}
