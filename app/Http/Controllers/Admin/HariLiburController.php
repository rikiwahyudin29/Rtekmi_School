<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HariLiburController extends Controller
{
    public function index()
    {
        $libur = HariLibur::orderBy('tanggal', 'desc')->get();
        return Inertia::render('Admin/Presensi/HariLibur', [
            'libur' => $libur
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();

        return redirect()->back()->with('success', 'Hari libur berhasil dihapus!');
    }

    public function syncApi()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-co-id' => env('API_CO_ID_KEY', '')
            ])->timeout(10)->get('https://use.api.co.id/indonesian-holidays', [
                'year' => date('Y')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $holidays = $data['data'] ?? $data;
                $inserted = 0;

                foreach ($holidays as $holiday) {
                    $tanggal = $holiday['date'] ?? $holiday['tanggal'] ?? null;
                    $keterangan = $holiday['name'] ?? $holiday['keterangan'] ?? 'Libur Nasional';

                    if ($tanggal) {
                        $exists = HariLibur::where('tanggal', $tanggal)->exists();
                        if (!$exists) {
                            HariLibur::create([
                                'tanggal' => $tanggal,
                                'keterangan' => $keterangan
                            ]);
                            $inserted++;
                        }
                    }
                }

                return redirect()->back()->with('success', "Berhasil sinkronisasi $inserted hari libur dari Pusat Data Nasional.");
            } else {
                return redirect()->back()->withErrors(['error' => 'Gagal mengambil data dari API. Pastikan API Key valid.']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Koneksi ke API terputus.']);
        }
    }
}
