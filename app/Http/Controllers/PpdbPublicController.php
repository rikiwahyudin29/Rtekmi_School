<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pendaftar;
use App\Models\WebProfil;
use App\Models\Sekolah;
use App\Services\WaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PpdbPublicController extends Controller
{
    public function create()
    {
        $webProfil = WebProfil::first();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first() ?? (object)[];
        $dataWeb = (object) array_merge((array) $sekolah, $webProfil ? $webProfil->toArray() : []);

        $jurusan = DB::table('tbl_jurusan')->get();

        return Inertia::render('Public/Ppdb/Register', [
            'web' => $dataWeb,
            'jurusanList' => $jurusan
        ]);
    }

    public function store(Request $request, WaService $waService)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'nisn' => 'required|numeric|digits_between:5,20',
            'nik' => 'required|numeric|digits:16',
            'jk' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:50',
            'tgl_lahir' => 'required|date',
            'agama' => 'required|string|max:50',
            'asal_sekolah' => 'required|string|max:100',
            'jurusan_minat' => 'required|string|max:50',
            'no_hp_siswa' => 'required|string|max:20',
            'alamat_jalan' => 'required|string',
            'rt_rw' => 'required|string|max:20',
            'desa_kelurahan' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            
            // Orang tua
            'nama_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'required|string|max:100',
            'no_hp_ortu' => 'required|string|max:20',
            
            // Dokumen
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'berkas_kk' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'berkas_ijazah' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120', // SKL / Ijazah
        ]);

        $data = $request->except(['foto', 'berkas_kk', 'berkas_ijazah']);

        // Generate No Pendaftaran (e.g. PPDB2026-0001)
        $year = date('Y');
        $lastPendaftar = Pendaftar::whereYear('tgl_daftar', $year)->orderBy('id', 'desc')->first();
        $urutan = $lastPendaftar ? ((int) substr($lastPendaftar->no_pendaftaran, -4)) + 1 : 1;
        $no_pendaftaran = 'PPDB' . $year . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
        
        $data['no_pendaftaran'] = $no_pendaftaran;
        $data['tgl_daftar'] = now();
        $data['status_pendaftaran'] = 'Pending';
        $data['is_migrated'] = false;
        
        // Gabungkan alamat lengkap
        $data['alamat'] = "{$data['alamat_jalan']}, RT/RW {$data['rt_rw']}, Ds/Kel. {$data['desa_kelurahan']}, Kec. {$data['kecamatan']}, Kab/Kota {$data['kabupaten']}, Prov. {$data['provinsi']}";

        // Upload files
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_foto_' . Str::slug($data['nama_lengkap']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ppdb/foto'), $filename);
            $data['foto'] = $filename;
        }

        if ($request->hasFile('berkas_kk')) {
            $file = $request->file('berkas_kk');
            $filename = time() . '_kk_' . Str::slug($data['nama_lengkap']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ppdb/dokumen'), $filename);
            $data['berkas_kk'] = $filename;
        }

        if ($request->hasFile('berkas_ijazah')) {
            $file = $request->file('berkas_ijazah');
            $filename = time() . '_ijazah_' . Str::slug($data['nama_lengkap']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ppdb/dokumen'), $filename);
            $data['berkas_ijazah'] = $filename;
        }

        Pendaftar::create($data);

        // Kirim WA Notifikasi
        $pesanWA = "Halo *{$data['nama_lengkap']}*,\n\n";
        $pesanWA .= "Terima kasih telah mendaftar di PPDB Online Sekolah Kami. Berikut adalah detail pendaftaran Anda:\n\n";
        $pesanWA .= "No. Pendaftaran : *{$no_pendaftaran}*\n";
        $pesanWA .= "Jurusan         : {$data['jurusan_minat']}\n";
        $pesanWA .= "Status          : *Pending (Menunggu Verifikasi)*\n\n";
        $pesanWA .= "Mohon simpan nomor pendaftaran ini dengan baik. Kami akan menghubungi Anda kembali setelah berkas diverifikasi oleh panitia.\n\n";
        $pesanWA .= "Salam,\nPanitia PPDB";

        $waService->kirim($data['no_hp_siswa'], $pesanWA);

        // Jika nomor ortu berbeda, kirim juga ke ortu (opsional, untuk amannya kirim ke siswa dulu)

        return redirect()->route('public.ppdb.success', ['no' => $no_pendaftaran])
                         ->with('message', 'Pendaftaran Berhasil! Nomor registrasi Anda: ' . $no_pendaftaran);
    }

    public function success(Request $request)
    {
        $no = $request->query('no');
        if (!$no) return redirect()->route('public.ppdb.create');

        $pendaftar = Pendaftar::where('no_pendaftaran', $no)->firstOrFail();
        
        $webProfil = WebProfil::first();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first() ?? (object)[];
        $dataWeb = (object) array_merge((array) $sekolah, $webProfil ? $webProfil->toArray() : []);

        return Inertia::render('Public/Ppdb/Success', [
            'pendaftar' => $pendaftar,
            'web' => $dataWeb
        ]);
    }
}
