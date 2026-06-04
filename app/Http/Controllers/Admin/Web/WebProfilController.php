<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\WebProfil;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebProfilController extends Controller
{
    public function index()
    {
        $profil = WebProfil::first();
        if (!$profil) {
            $profil = WebProfil::create([
                'nama_kepsek' => 'Nama Kepala Sekolah',
                'sambutan_kepsek' => 'Selamat datang di website kami.',
            ]);
        }
        return Inertia::render('Admin/Web/Profil/Index', [
            'profil' => $profil
        ]);
    }

    public function update(Request $request)
    {
        $profil = WebProfil::first();
        
        $data = $request->validate([
            'deskripsi_hero' => 'nullable|string',
            'nama_kepsek' => 'nullable|string|max:100',
            'sambutan_kepsek' => 'nullable|string',
            'link_fb' => 'nullable|string',
            'link_ig' => 'nullable|string',
            'link_yt' => 'nullable|string',
            'link_map' => 'nullable|string',
        ]);

        if ($request->hasFile('foto_kepsek')) {
            $file = $request->file('foto_kepsek');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profil'), $filename);
            $data['foto_kepsek'] = $filename;
        }

        if ($request->hasFile('spot_hero_png')) {
            $file = $request->file('spot_hero_png');
            $filename = time() . '_hero_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profil'), $filename);
            $data['spot_hero_png'] = $filename;
        }

        if ($request->hasFile('spot_ppdb_png')) {
            $file = $request->file('spot_ppdb_png');
            $filename = time() . '_ppdb_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profil'), $filename);
            $data['spot_ppdb_png'] = $filename;
        }

        $profil->update($data);

        return redirect()->back()->with('message', 'Profil Web berhasil diperbarui.');
    }
}
