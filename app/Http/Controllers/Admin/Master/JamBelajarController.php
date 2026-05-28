<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\JamSekolah;
use App\Models\JamMaster;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JamBelajarController extends Controller
{
    public function index()
    {
        $jamSekolah = JamSekolah::first() ?? new JamSekolah();
        $jamMaster = JamMaster::orderBy('urutan', 'asc')->get();

        return Inertia::render('Admin/Master/JamBelajar/Index', [
            'jamSekolah' => $jamSekolah,
            'jamMaster' => $jamMaster,
        ]);
    }

    public function updateSekolah(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk_mulai' => 'required',
            'jam_masuk_akhir' => 'required',
            'jam_pulang_mulai' => 'required',
            'latitude' => 'nullable|string|max:100',
            'longitude' => 'nullable|string|max:100',
            'radius' => 'nullable|integer',
            'qr_token' => 'nullable|string',
        ]);

        $jamSekolah = JamSekolah::first();
        if ($jamSekolah) {
            $jamSekolah->update($validated);
        } else {
            JamSekolah::create($validated);
        }

        return back()->with('message', 'Pengaturan Jam & Lokasi Sekolah berhasil diperbarui.');
    }

    public function storeMaster(Request $request)
    {
        $validated = $request->validate([
            'urutan' => 'required|integer',
            'nama_jam' => 'required|string|max:50',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'is_istirahat' => 'nullable|boolean',
        ]);

        JamMaster::create($validated);

        return back()->with('message', 'Jam Belajar berhasil ditambahkan.');
    }

    public function updateMaster(Request $request, $id)
    {
        $jam = JamMaster::findOrFail($id);

        $validated = $request->validate([
            'urutan' => 'required|integer',
            'nama_jam' => 'required|string|max:50',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'is_istirahat' => 'nullable|boolean',
        ]);

        $jam->update($validated);

        return back()->with('message', 'Jam Belajar berhasil diperbarui.');
    }

    public function destroyMaster($id)
    {
        JamMaster::findOrFail($id)->delete();
        return back()->with('message', 'Jam Belajar berhasil dihapus.');
    }
}
