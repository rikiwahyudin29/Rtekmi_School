<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EkskulController extends Controller
{
    public function index()
    {
        // Ambil semua data Ekskul
        $ekskul = DB::table('tbl_ekskul')
            ->orderBy('kategori', 'desc')
            ->orderBy('nama_ekskul', 'asc')
            ->get();
        
        $data_ekskul = [];
        foreach ($ekskul as $e) {
            // Ambil data Pembina untuk setiap Ekskul
            $pembina = DB::table('tbl_ekskul_pembina')
                ->select('tbl_ekskul_pembina.*', 'tbl_guru.nama_lengkap as nama_guru')
                ->join('tbl_guru', 'tbl_guru.id', '=', 'tbl_ekskul_pembina.guru_id')
                ->where('tbl_ekskul_pembina.ekskul_id', $e->id)
                ->get();
            
            // Hitung jumlah anggota aktif
            $jml_anggota = DB::table('tbl_ekskul_anggota')
                ->where('ekskul_id', $e->id)
                ->where('status_anggota', 'Approved')
                ->count();

            $e->pembina = $pembina;
            $e->jml_anggota = $jml_anggota;
            $data_ekskul[] = $e;
        }

        // Ambil list guru untuk dropdown tambah pembina
        $list_guru = DB::table('tbl_guru')->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Ekskul/Index', [
            'ekskul' => $data_ekskul,
            'list_guru' => $list_guru
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'nama_ekskul' => 'required|string',
            'kategori' => 'required|in:Wajib,Pilihan',
            'hari' => 'required|string',
            'jam' => 'required|string',
            'logo' => 'nullable|image|max:2048'
        ]);

        $namaLogo = 'default_ekskul.png';

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $dir = public_path('uploads/ekskul');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $file = $request->file('logo');
            $namaLogo = $file->hashName();
            $file->move($dir, $namaLogo);
        }

        DB::table('tbl_ekskul')->insert([
            'nama_ekskul' => $request->input('nama_ekskul'),
            'kategori'    => $request->input('kategori'),
            'hari'        => $request->input('hari'),
            'jam'         => $request->input('jam'),
            'kuota'       => $request->input('kuota') ?: null,
            'visi_misi'   => $request->input('visi_misi'),
            'logo'        => $namaLogo,
            'status'      => $request->input('status', 'Aktif')
        ]);

        return redirect()->route('admin.ekskul.index')->with('message', 'Unit Ekstrakurikuler berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $request->validate([
            'nama_ekskul' => 'required|string',
            'kategori' => 'required|in:Wajib,Pilihan',
            'hari' => 'required|string',
            'jam' => 'required|string',
            'logo' => 'nullable|image|max:2048'
        ]);
        
        $data = [
            'nama_ekskul' => $request->input('nama_ekskul'),
            'kategori'    => $request->input('kategori'),
            'hari'        => $request->input('hari'),
            'jam'         => $request->input('jam'),
            'kuota'       => $request->input('kuota') ?: null,
            'visi_misi'   => $request->input('visi_misi'),
            'status'      => $request->input('status', 'Aktif')
        ];

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $dir = public_path('uploads/ekskul');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $file = $request->file('logo');
            $namaLogo = $file->hashName();
            $file->move($dir, $namaLogo);
            $data['logo'] = $namaLogo;
        }

        DB::table('tbl_ekskul')->where('id', $id)->update($data);
        
        return redirect()->route('admin.ekskul.index')->with('message', 'Data Ekstrakurikuler berhasil diperbarui!');
    }

    public function delete($id)
    {
        DB::table('tbl_ekskul')->where('id', $id)->delete();
        DB::table('tbl_ekskul_pembina')->where('ekskul_id', $id)->delete();
        
        return redirect()->route('admin.ekskul.index')->with('message', 'Unit Ekstrakurikuler berhasil dihapus!');
    }

    public function tambahPembina(Request $request)
    {
        $ekskul_id = $request->input('ekskul_id');
        $guru_id = $request->input('guru_id');

        $cek = DB::table('tbl_ekskul_pembina')
            ->where('ekskul_id', $ekskul_id)
            ->where('guru_id', $guru_id)
            ->count();
            
        if($cek > 0) {
            return redirect()->route('admin.ekskul.index')->with('error', 'Guru tersebut sudah menjadi pembina di Unit Ekskul ini!');
        }

        DB::table('tbl_ekskul_pembina')->insert([
            'ekskul_id' => $ekskul_id,
            'guru_id' => $guru_id,
            'status' => 'Aktif'
        ]);

        return redirect()->route('admin.ekskul.index')->with('message', 'Guru Pembina berhasil ditugaskan!');
    }

    public function hapusPembina($id)
    {
        DB::table('tbl_ekskul_pembina')->where('id', $id)->delete();
        return redirect()->route('admin.ekskul.index')->with('message', 'Tugas Guru Pembina berhasil dicabut!');
    }
}
