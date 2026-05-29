<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\JenisPengeluaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $pengeluaran = Pengeluaran::with(['jenis', 'petugas'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        $jenis = JenisPengeluaran::orderBy('nama_jenis', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Pengeluaran/Index', [
            'pengeluaran' => $pengeluaran,
            'jenis'       => $jenis,
            'bulan'       => $bulan,
            'tahun'       => $tahun
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_jenis' => 'required|integer',
            'judul_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required',
            'tanggal' => 'required|date'
        ]);

        $nominal = str_replace('.', '', $request->nominal);

        Pengeluaran::create([
            'id_divisi' => 1, // Default, can be updated later if needed
            'id_jenis'  => $request->id_jenis,
            'judul_pengeluaran' => $request->judul_pengeluaran,
            'nominal'   => $nominal,
            'tanggal'   => $request->tanggal,
            'keterangan' => $request->keterangan,
            'petugas_id' => auth()->id()
        ]);

        return back()->with('message', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $pengeluaran->delete();
        
        return back()->with('message', 'Pengeluaran berhasil dihapus.');
    }
}
