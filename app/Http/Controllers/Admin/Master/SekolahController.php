<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SekolahController extends Controller
{
    public function index()
    {
        $sekolah = Sekolah::first() ?? new Sekolah();

        return Inertia::render('Admin/Master/Sekolah/Index', [
            'sekolah' => $sekolah,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:100',
            'npsn' => 'required|string|max:10',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'alamat' => 'nullable|string',
            'desa_kelurahan' => 'nullable|string|max:50',
            'kecamatan' => 'nullable|string|max:50',
            'kabupaten' => 'nullable|string|max:50',
            'provinsi' => 'nullable|string|max:50',
            'kode_pos' => 'nullable|string|max:10',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:50',
            'slogan_sekolah' => 'nullable|string',
            
            // Kepsek
            'nama_kepsek' => 'nullable|string|max:150',
            'nip_kepsek' => 'nullable|string|max:30',
            'sambutan_kepsek' => 'nullable|string',

            // Integrasi API
            'google_client_id' => 'nullable|string',
            'google_client_secret' => 'nullable|string',
            'wa_api_url' => 'nullable|string',
            'wa_api_token' => 'nullable|string',
            'tele_bot_token' => 'nullable|string',
            'tele_chat_id' => 'nullable|string|max:100',
            'tripay_api_key' => 'nullable|string',
            'tripay_private_key' => 'nullable|string',
            'tripay_merchant_code' => 'nullable|string|max:50',
            'mode_transaksi' => 'nullable|in:Sandbox,Production',

            // Images
            'logo' => 'nullable|image|max:2048',
            'kop_surat' => 'nullable|image|max:2048',
            'ttd_kepsek' => 'nullable|image|max:2048',
            'foto_kepsek' => 'nullable|image|max:2048',
        ]);

        $sekolah = Sekolah::first();
        if (!$sekolah) {
            $sekolah = new Sekolah();
        }

        // Handle File Uploads
        $fileFields = ['logo', 'kop_surat', 'ttd_kepsek', 'foto_kepsek'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists and not default
                if ($sekolah->$field && !str_contains($sekolah->$field, 'default')) {
                    $oldPath = public_path('uploads/identitas/' . $sekolah->$field);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                
                $file = $request->file($field);
                $filename = $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/identitas'), $filename);
                $validated[$field] = $filename;
            } else {
                unset($validated[$field]); // Keep existing if not uploaded
            }
        }

        if ($sekolah->exists) {
            $sekolah->update($validated);
        } else {
            Sekolah::create($validated);
        }

        return back()->with('message', 'Identitas Sekolah berhasil diperbarui.');
    }
}
