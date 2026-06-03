<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\JenisPengeluaran;
use App\Models\Divisi;
use App\Models\LogKeuangan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $pengeluaran = Pengeluaran::with(['jenis', 'divisi', 'petugas'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $jenis = JenisPengeluaran::orderBy('nama_jenis', 'asc')->get();
        $divisi = Divisi::orderBy('nama_divisi', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Pengeluaran/Index', [
            'pengeluaran' => $pengeluaran,
            'jenis'       => $jenis,
            'divisi'      => $divisi,
            'bulan'       => $bulan,
            'tahun'       => $tahun
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_divisi' => 'required|integer',
            'id_jenis' => 'required|integer',
            'judul_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required',
            'tanggal' => 'required|date'
        ]);

        $nominal = str_replace('.', '', $request->nominal);

        Pengeluaran::create([
            'id_divisi' => $request->id_divisi,
            'id_jenis'  => $request->id_jenis,
            'judul_pengeluaran' => $request->judul_pengeluaran,
            'nominal'   => $nominal,
            'tanggal'   => $request->tanggal,
            'keterangan' => $request->keterangan,
            'petugas_id' => auth()->id()
        ]);

        // Rekam Log Aktivitas
        $rp = number_format($nominal, 0, ',', '.');
        LogKeuangan::create([
            'aksi' => "Mencatat Pengeluaran: {$request->judul_pengeluaran} sebesar Rp $rp",
            'user_id' => auth()->id(),
            'nama_user' => auth()->user()->nama_lengkap ?? auth()->user()->username,
            'role' => auth()->user()->role,
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('message', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        $judul = $pengeluaran->judul_pengeluaran;
        $rp = number_format($pengeluaran->nominal, 0, ',', '.');

        $pengeluaran->delete();
        
        // Rekam Log
        LogKeuangan::create([
            'aksi' => "MENGHAPUS Pengeluaran: $judul (Rp $rp)",
            'user_id' => auth()->id(),
            'nama_user' => auth()->user()->nama_lengkap ?? auth()->user()->username,
            'role' => auth()->user()->role,
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('message', 'Pengeluaran berhasil dihapus.');
    }

    public function storeDivisi(Request $request)
    {
        $request->validate(['nama_divisi' => 'required|string|max:255']);
        Divisi::create(['nama_divisi' => $request->nama_divisi]);

        LogKeuangan::create([
            'aksi' => "Menambah Master Divisi: {$request->nama_divisi}",
            'user_id' => auth()->id(),
            'nama_user' => auth()->user()->nama_lengkap ?? auth()->user()->username,
            'role' => auth()->user()->role,
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('message', 'Divisi baru ditambahkan.');
    }

    public function storeJenis(Request $request)
    {
        $request->validate(['nama_jenis' => 'required|string|max:255']);
        JenisPengeluaran::create(['nama_jenis' => $request->nama_jenis]);

        LogKeuangan::create([
            'aksi' => "Menambah Master Jenis Pengeluaran: {$request->nama_jenis}",
            'user_id' => auth()->id(),
            'nama_user' => auth()->user()->nama_lengkap ?? auth()->user()->username,
            'role' => auth()->user()->role,
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('message', 'Jenis Pengeluaran baru ditambahkan.');
    }
}
