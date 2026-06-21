<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BukuTamu;
use App\Models\Sekolah;
use App\Services\WaService;
use Carbon\Carbon;

class BukuTamuController extends Controller
{
    public function index()
    {
        $sekolah = Sekolah::first();
        return Inertia::render('Public/BukuTamu/Index', [
            'title' => 'Buku Tamu & Antrian Lobi',
            'sekolah' => $sekolah
        ]);
    }

    public function store(Request $request, WaService $wa)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'keperluan' => 'required|string',
            'kategori' => 'required|string',
            'instansi_asal' => 'nullable|string|max:255'
        ]);

        $tanggal_hari_ini = Carbon::now()->format('Y-m-d');
        
        $last_antrian = BukuTamu::where('tanggal', $tanggal_hari_ini)->max('no_antrian');
        $no_antrian_baru = $last_antrian ? $last_antrian + 1 : 1;

        $tamu = BukuTamu::create([
            'tanggal' => $tanggal_hari_ini,
            'no_antrian' => $no_antrian_baru,
            'nama_lengkap' => $request->nama_lengkap,
            'instansi_asal' => $request->instansi_asal,
            'no_hp' => $request->no_hp,
            'keperluan' => $request->keperluan,
            'kategori' => $request->kategori,
            'status' => 'Menunggu'
        ]);

        $sekolah = Sekolah::find(1);
        $namaSekolah = $sekolah ? $sekolah->nama_sekolah : 'Sekolah';

        if (!empty($request->no_hp)) {
            $jam = Carbon::now()->format('H:i');
            $tgl = Carbon::now()->format('d-m-Y');
            
            $pesanWA = "🎫 *KONFIRMASI ANTRIAN LOBI* 🎫\n\n";
            $pesanWA .= "Halo, *" . $request->nama_lengkap . "*.\n";
            $pesanWA .= "Terima kasih telah mendaftar di buku tamu digital kami.\n\n";
            $pesanWA .= "🏷️ Kategori: *" . $request->kategori . "*\n";
            $pesanWA .= "🔢 Nomor Antrian: *#" . $no_antrian_baru . "*\n";
            $pesanWA .= "📝 Keperluan: " . $request->keperluan . "\n";
            $pesanWA .= "📅 Tanggal: " . $tgl . " | " . $jam . " WIB\n\n";
            $pesanWA .= "Silakan tunggu panggilan di area lobi.\n";
            $pesanWA .= "_Sistem Layanan " . $namaSekolah . "_";

            $wa->kirim($request->no_hp, $pesanWA);
        }

        return redirect()->back()->with([
            'success' => 'Berhasil mengambil antrian!',
            'antrian' => $no_antrian_baru,
            'tamu_id' => $tamu->id
        ]);
    }

    public function cetak($id)
    {
        $tamu = BukuTamu::findOrFail($id);
        $sekolah = Sekolah::find(1);

        return view('cetak.antrian_tamu', compact('tamu', 'sekolah'));
    }
}
