<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JamSekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JamPresensiController extends Controller
{
    public function index()
    {
        $jam = JamSekolah::find(1);

        if (!$jam) {
            $jam = JamSekolah::create([
                'id' => 1,
                'jam_masuk_mulai' => '06:00:00',
                'jam_masuk_akhir' => '07:15:00',
                'jam_pulang_mulai' => '14:00:00',
                'latitude' => '-6.200000',
                'longitude' => '106.816666',
                'radius' => 50,
                'qr_token' => 'SMK_TOKEN_123'
            ]);
        }

        return Inertia::render('Admin/Presensi/SettingJam', [
            'jam' => $jam
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk_mulai' => 'required',
            'jam_masuk_akhir' => 'required',
            'jam_pulang_mulai' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|numeric',
            'qr_token' => 'required|string',
        ]);

        $jam = JamSekolah::find(1);
        if ($jam) {
            $jam->update([
                'jam_masuk_mulai' => $request->jam_masuk_mulai,
                'jam_masuk_akhir' => $request->jam_masuk_akhir,
                'jam_pulang_mulai' => $request->jam_pulang_mulai,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
                'qr_token' => $request->qr_token,
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan Jam & Lokasi Presensi berhasil diperbarui!');
    }
}
