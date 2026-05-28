<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Ekskul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $kelasFilter = $request->input('kelas_id');
        $statusFilter = $request->input('status');

        $siswa = Siswa::with(['kelas', 'jurusan', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->when($kelasFilter, function ($query, $kelasFilter) {
                $query->where('kelas_id', $kelasFilter);
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                $query->where('status_siswa', $statusFilter);
            })
            ->orderBy('nama_lengkap', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $data_kelas = Kelas::all();

        return Inertia::render('Admin/Siswa/Index', [
            'siswa' => $siswa,
            'data_kelas' => $data_kelas,
            'filters' => $request->only('search', 'per_page', 'kelas_id', 'status')
        ]);
    }

    public function create()
    {
        $data_kelas = Kelas::all();
        $data_jurusan = Jurusan::all();
        $data_ekskul = Ekskul::all();

        return Inertia::render('Admin/Siswa/Create', [
            'data_kelas' => $data_kelas,
            'data_jurusan' => $data_jurusan,
            'data_ekskul' => $data_ekskul
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn'           => 'required|unique:tbl_siswa,nisn',
            'nama_lengkap'   => 'required|string|max:100',
            'kelas_id'       => 'required|exists:tbl_kelas,id',
            'foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for siswa
            $user = User::create([
                'username'      => $request->nisn,
                'email'         => $request->email_siswa,
                'password'      => Hash::make($request->nisn), // default password = NISN
                'nama_lengkap'  => $request->nama_lengkap,
                'nomor_wa'      => $request->no_hp_siswa,
                'role'          => 'siswa',
            ]);

            // Handle foto upload
            $fotoName = 'default.png';
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $dir = public_path('uploads/siswa');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $fotoName = time() . '_' . uniqid() . '.' . $request->foto->getClientOriginalExtension();
                $request->foto->move($dir, $fotoName);
            }

            Siswa::create([
                'user_id'          => $user->id,
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama_lengkap'     => $request->nama_lengkap,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'agama'            => $request->agama,
                'nik'              => $request->nik,
                'alamat'           => $request->alamat,
                'no_hp_siswa'      => $request->no_hp_siswa,
                'email_siswa'      => $request->email_siswa,
                'kelas_id'         => $request->kelas_id,
                'jurusan_id'       => $request->jurusan_id,
                'ekskul_id'        => $request->ekskul_id,
                'sekolah_asal'     => $request->sekolah_asal,
                'tahun_angkatan'   => $request->tahun_angkatan,
                'nama_ayah'        => $request->nama_ayah,
                'nama_ibu'         => $request->nama_ibu,
                'nama_wali'        => $request->nama_wali,
                'pekerjaan_ayah'   => $request->pekerjaan_ayah,
                'pekerjaan_ibu'    => $request->pekerjaan_ibu,
                'pekerjaan_wali'   => $request->pekerjaan_wali,
                'no_hp_ortu'       => $request->no_hp_ortu,
                'status_siswa'     => 'Aktif',
                'foto'             => $fotoName,
            ]);

            DB::commit();
            return redirect()->route('admin.siswa.index')->with('message', '✅ Data siswa <b>' . $request->nama_lengkap . '</b> berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $siswa = Siswa::with(['kelas', 'jurusan', 'user'])->findOrFail($id);
        $data_kelas = Kelas::all();
        $data_jurusan = Jurusan::all();
        $data_ekskul = Ekskul::all();

        return Inertia::render('Admin/Siswa/Edit', [
            'siswa' => $siswa,
            'data_kelas' => $data_kelas,
            'data_jurusan' => $data_jurusan,
            'data_ekskul' => $data_ekskul
        ]);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $validated = $request->validate([
            'nisn'           => 'required|unique:tbl_siswa,nisn,' . $id,
            'nama_lengkap'   => 'required|string|max:100',
            'kelas_id'       => 'required|exists:tbl_kelas,id',
            'foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle foto upload
        $fotoName = $siswa->foto;
        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $dir = public_path('uploads/siswa');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            // Delete old foto if not default
            if ($siswa->foto && $siswa->foto !== 'default.png' && file_exists($dir . '/' . $siswa->foto)) {
                unlink($dir . '/' . $siswa->foto);
            }
            $fotoName = time() . '_' . uniqid() . '.' . $request->foto->getClientOriginalExtension();
            $request->foto->move($dir, $fotoName);
        }

        $siswa->update([
            'nisn'             => $request->nisn,
            'nis'              => $request->nis,
            'nama_lengkap'     => $request->nama_lengkap,
            'jenis_kelamin'    => $request->jenis_kelamin,
            'tempat_lahir'     => $request->tempat_lahir,
            'tanggal_lahir'    => $request->tanggal_lahir,
            'agama'            => $request->agama,
            'nik'              => $request->nik,
            'alamat'           => $request->alamat,
            'no_hp_siswa'      => $request->no_hp_siswa,
            'email_siswa'      => $request->email_siswa,
            'kelas_id'         => $request->kelas_id,
            'jurusan_id'       => $request->jurusan_id,
            'ekskul_id'        => $request->ekskul_id,
            'sekolah_asal'     => $request->sekolah_asal,
            'tahun_angkatan'   => $request->tahun_angkatan,
            'nama_ayah'        => $request->nama_ayah,
            'nama_ibu'         => $request->nama_ibu,
            'nama_wali'        => $request->nama_wali,
            'pekerjaan_ayah'   => $request->pekerjaan_ayah,
            'pekerjaan_ibu'    => $request->pekerjaan_ibu,
            'pekerjaan_wali'   => $request->pekerjaan_wali,
            'no_hp_ortu'       => $request->no_hp_ortu,
            'foto'             => $fotoName,
        ]);

        // Update user account
        if ($siswa->user) {
            $siswa->user->update([
                'nama_lengkap' => $request->nama_lengkap,
                'email'        => $request->email_siswa,
                'nomor_wa'     => $request->no_hp_siswa,
            ]);
        }

        return redirect()->route('admin.siswa.index')->with('message', '✅ Data siswa <b>' . $request->nama_lengkap . '</b> berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        
        // Delete user account if exists
        if ($siswa->user_id) {
            User::find($siswa->user_id)?->delete();
        }
        
        // Delete foto if not default
        if ($siswa->foto && $siswa->foto !== 'default.png' && file_exists(public_path('uploads/siswa/' . $siswa->foto))) {
            unlink(public_path('uploads/siswa/' . $siswa->foto));
        }

        $siswa->delete();

        return redirect()->route('admin.siswa.index')->with('message', '✅ Data siswa berhasil dihapus.');
    }

    public function exportExcel()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="data_siswa.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NISN', 'NIS', 'NamaLengkap', 'JenisKelamin', 'TempatLahir', 'TanggalLahir', 'Agama', 'Kelas', 'Alamat', 'NoHP', 'Email', 'NamaAyah', 'NamaIbu', 'NoHPOrtu', 'StatusSiswa']);
            
            $siswa = Siswa::with('kelas')->get();
            foreach ($siswa as $s) {
                fputcsv($file, [
                    $s->nisn,
                    $s->nis,
                    $s->nama_lengkap,
                    $s->jenis_kelamin,
                    $s->tempat_lahir,
                    $s->tanggal_lahir,
                    $s->agama,
                    $s->kelas?->nama_kelas,
                    $s->alamat,
                    $s->no_hp_siswa,
                    $s->email_siswa,
                    $s->nama_ayah,
                    $s->nama_ibu,
                    $s->no_hp_ortu,
                    $s->status_siswa,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function templateExcel()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_siswa.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NISN', 'NIS', 'NamaLengkap', 'JenisKelamin', 'TempatLahir', 'TanggalLahir', 'Agama', 'Alamat', 'NoHPSiswa', 'Email', 'SekolahAsal', 'NamaAyah', 'NamaIbu', 'NoHPOrtu']);
            fputcsv($file, ['0012345678', '12345', 'Ahmad Fauzi', 'L', 'Jakarta', '2008-05-15', 'Islam', 'Jl. Merdeka No.10', '081234567890', 'ahmad@email.com', 'SMP Negeri 1', 'Budi', 'Siti', '082345678901']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), 'r');
            $header = fgetcsv($handle); // skip header
            $imported = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 3 && !empty($row[0])) {
                    // Create user
                    $user = User::create([
                        'username'      => $row[0], // NISN
                        'email'         => $row[9] ?? null,
                        'password'      => Hash::make($row[0]),
                        'nama_lengkap'  => $row[2],
                        'nomor_wa'      => $row[8] ?? null,
                        'role'          => 'siswa',
                    ]);

                    Siswa::create([
                        'user_id'        => $user->id,
                        'nisn'           => $row[0],
                        'nis'            => $row[1] ?? null,
                        'nama_lengkap'   => $row[2],
                        'jenis_kelamin'  => $row[3] ?? 'L',
                        'tempat_lahir'   => $row[4] ?? null,
                        'tanggal_lahir'  => $row[5] ?? null,
                        'agama'          => $row[6] ?? null,
                        'alamat'         => $row[7] ?? null,
                        'no_hp_siswa'    => $row[8] ?? null,
                        'email_siswa'    => $row[9] ?? null,
                        'sekolah_asal'   => $row[10] ?? null,
                        'nama_ayah'      => $row[11] ?? null,
                        'nama_ibu'       => $row[12] ?? null,
                        'no_hp_ortu'     => $row[13] ?? null,
                        'status_siswa'   => 'Aktif',
                    ]);
                    $imported++;
                }
            }
            fclose($handle);

            return redirect()->route('admin.siswa.index')->with('message', "✅ Berhasil mengimpor <b>{$imported}</b> data siswa.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
