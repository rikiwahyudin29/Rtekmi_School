<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $gurus = Guru::with('user.roles')
            ->when($search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama_lengkap', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::all();

        return Inertia::render('Admin/Guru/Index', [
            'gurus' => $gurus,
            'available_roles' => $roles,
            'filters' => $request->only('search', 'per_page')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Guru/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'                 => 'required|unique:tbl_guru,nik',
            'nama_lengkap'        => 'required|string|max:100',
            'nip'                 => 'nullable|string',
            'nuptk'               => 'nullable|string',
            'dapodik_id'          => 'nullable|string',
            'tempat_lahir'        => 'nullable|string',
            'tanggal_lahir'       => 'nullable|date',
            'ibu_kandung'         => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string',
            'sertifikasi'         => 'nullable|string',
            'status_guru'         => 'nullable|string',
            'status_kepegawaian'  => 'nullable|string',
            'email'               => 'nullable|email',
            'nomor_wa'            => 'nullable|string',
            'foto'                => 'nullable|image|max:2048'
        ]);

        $passwordAsli = Str::random(8);

        DB::beginTransaction();
        try {
            // Create User
            $userEmail = $validated['email'] ?? ($validated['nik'] . '@sekolah.id');
            $user = User::create([
                'username'     => $validated['nik'],
                'password'     => Hash::make($passwordAsli),
                'email'        => $userEmail,
                'nama_lengkap' => $validated['nama_lengkap'],
                'nomor_wa'     => $validated['nomor_wa'] ?? null,
                'role'         => 'guru',
            ]);

            // Assign Guru Role
            $roleGuru = Role::where('role_key', 'guru')->first();
            if ($roleGuru) {
                $user->roles()->attach($roleGuru->id);
            }

            // Handle Upload
            $namaFoto = 'default.png';
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $dir = public_path('uploads/guru');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file = $request->file('foto');
                $namaFoto = $file->hashName();
                $file->move($dir, $namaFoto);
            }

            // Create Guru
            Guru::create([
                'user_id'             => $user->id,
                'nik'                 => $validated['nik'],
                'nip'                 => $request->nip,
                'nuptk'               => $request->nuptk,
                'dapodik_id'          => $request->dapodik_id,
                'nama_lengkap'        => $validated['nama_lengkap'],
                'gelar_depan'         => $request->gelar_depan,
                'gelar_belakang'      => $request->gelar_belakang,
                'jk'                  => $request->jenis_kelamin ?? 'L',
                'jenis_kelamin'       => $request->jenis_kelamin ?? 'L',
                'tempat_lahir'        => $request->tempat_lahir,
                'tanggal_lahir'       => $request->tanggal_lahir,
                'tgl_lahir'           => $request->tanggal_lahir,
                'ibu_kandung'         => $request->ibu_kandung,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'sertifikasi'         => $request->sertifikasi,
                'status_guru'         => $request->status_guru,
                'status_kepegawaian'  => $request->status_kepegawaian,
                'email'               => $userEmail,
                'no_hp'               => $request->nomor_wa,
                'alamat'              => $request->alamat,
                'foto'                => $namaFoto
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')
                ->with('message', "Berhasil! Username: {$validated['nik']}, Password: {$passwordAsli}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        
        // Remove photo if not default
        if ($guru->foto !== 'default.png' && file_exists(public_path('uploads/guru/' . $guru->foto))) {
            @unlink(public_path('uploads/guru/' . $guru->foto));
        }

        $userId = $guru->user_id;
        $guru->delete();

        if ($guru->user_id) {
            User::where('id', $userId)->delete();
        }
        
        return back()->with('message', 'Data guru berhasil dihapus.');
    }

    public function edit($id)
    {
        $guru = Guru::with('user')->findOrFail($id);
        return Inertia::render('Admin/Guru/Edit', [
            'guru' => $guru
        ]);
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        
        $validated = $request->validate([
            'nik'                 => 'required|unique:tbl_guru,nik,' . $id,
            'nama_lengkap'        => 'required|string|max:100',
            'nip'                 => 'nullable|string',
            'nuptk'               => 'nullable|string',
            'dapodik_id'          => 'nullable|string',
            'tempat_lahir'        => 'nullable|string',
            'tanggal_lahir'       => 'nullable|date',
            'ibu_kandung'         => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string',
            'sertifikasi'         => 'nullable|string',
            'status_guru'         => 'nullable|string',
            'status_kepegawaian'  => 'nullable|string',
            'email'               => 'nullable|email',
            'nomor_wa'            => 'nullable|string',
            'foto'                => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Handle Upload
            $namaFoto = $guru->foto;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $dir = public_path('uploads/guru');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                if ($namaFoto !== 'default.png' && file_exists($dir . '/' . $namaFoto)) {
                    @unlink($dir . '/' . $namaFoto);
                }
                
                $file = $request->file('foto');
                $namaFoto = $file->hashName();
                $file->move($dir, $namaFoto);
            }

            $userEmail = $validated['email'] ?? ($validated['nik'] . '@sekolah.id');

            $guru->update([
                'nik'                 => $validated['nik'],
                'nip'                 => $request->nip,
                'nuptk'               => $request->nuptk,
                'dapodik_id'          => $request->dapodik_id,
                'nama_lengkap'        => $validated['nama_lengkap'],
                'gelar_depan'         => $request->gelar_depan,
                'gelar_belakang'      => $request->gelar_belakang,
                'jk'                  => $request->jenis_kelamin ?? 'L',
                'jenis_kelamin'       => $request->jenis_kelamin ?? 'L',
                'tempat_lahir'        => $request->tempat_lahir,
                'tanggal_lahir'       => $request->tanggal_lahir,
                'tgl_lahir'           => $request->tanggal_lahir,
                'ibu_kandung'         => $request->ibu_kandung,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'sertifikasi'         => $request->sertifikasi,
                'status_guru'         => $request->status_guru,
                'status_kepegawaian'  => $request->status_kepegawaian,
                'email'               => $userEmail,
                'no_hp'               => $request->nomor_wa,
                'alamat'              => $request->alamat,
                'foto'                => $namaFoto
            ]);

            if ($guru->user_id) {
                $user = User::find($guru->user_id);
                if ($user) {
                    $user->update([
                        'username'     => $validated['nik'],
                        'email'        => $userEmail,
                        'nama_lengkap' => $validated['nama_lengkap'],
                        'nomor_wa'     => $validated['nomor_wa'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.guru.index')->with('message', 'Data guru berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reset2FA($id)
    {
        $guru = Guru::findOrFail($id);
        
        if ($guru->user_id) {
            $user = User::find($guru->user_id);
            if ($user) {
                $user->update(['google2fa_secret' => null]);
                return back()->with('message', 'Google Authenticator untuk guru ini berhasil di-reset.');
            }
        }
        
        return back()->with('error', 'Gagal mereset! Guru ini tidak tertaut dengan akun pengguna (User ID kosong).');
    }

    public function resetPassword($id)
    {
        $guru = Guru::findOrFail($id);
        
        if ($guru->user_id) {
            $user = User::find($guru->user_id);
            if ($user) {
                $user->update([
                    'password' => Hash::make($guru->nik)
                ]);
                return back()->with('message', "Password guru berhasil di-reset menjadi NIK: {$guru->nik}");
            }
        }
        
        return back()->with('error', 'Gagal mereset! Guru ini tidak tertaut dengan akun pengguna.');
    }

    public function syncRoles(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        
        $request->validate([
            'roles' => 'array',
        ]);

        $user = null;
        if ($guru->user_id) {
            $user = User::find($guru->user_id);
        }

        // AUTO HEAL: Jika user_id kosong atau user tidak ditemukan di tabel users
        if (!$user) {
            $passwordAsli = Str::random(8);
            $userEmail = $guru->email ?? ($guru->nik . '@sekolah.id');
            
            // Cek apakah username dengan NIK ini sudah ada di tabel users
            $user = User::where('username', $guru->nik)->first();
            
            if (!$user) {
                $user = User::create([
                    'username'     => $guru->nik,
                    'password'     => Hash::make($passwordAsli),
                    'email'        => $userEmail,
                    'nama_lengkap' => $guru->nama_lengkap,
                    'nomor_wa'     => $guru->nomor_wa ?? null,
                    'role'         => 'guru',
                ]);
            }
            
            // Tautkan kembali user_id ke tabel guru
            $guru->update(['user_id' => $user->id]);
            
            // Berikan role dasar guru
            $roleGuru = Role::where('role_key', 'guru')->first();
            if ($roleGuru && !$user->roles->contains($roleGuru->id)) {
                $user->roles()->attach($roleGuru->id);
            }
        }

        if ($user) {
            // Ensure the base 'guru' role is always kept
            $rolesToSync = $request->roles ?? [];
            $roleGuru = Role::where('role_key', 'guru')->first();
            if ($roleGuru && !in_array($roleGuru->id, $rolesToSync)) {
                $rolesToSync[] = $roleGuru->id;
            }
            
            $user->roles()->sync($rolesToSync);
            return back()->with('message', 'Akun berhasil ditautkan & Tugas tambahan guru diperbarui.');
        }
        
        return back()->with('error', 'Gagal membuat atau mengaitkan akun pengguna.');
    }

    public function templateExcel()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_guru.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NIK', 'NIP', 'NamaLengkap', 'GelarDepan', 'GelarBelakang', 'JenisKelamin', 'NomorWA', 'Alamat']);
            fputcsv($file, ['3201234567890001', '123456789', 'Budi Santoso', 'Drs.', 'M.Pd', 'L', '081234567890', 'Jl. Pendidikan No. 1']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="data_guru.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Add headers
            fputcsv($file, ['NIK', 'NIP', 'NamaLengkap', 'GelarDepan', 'GelarBelakang', 'JenisKelamin', 'TempatLahir', 'TanggalLahir', 'StatusGuru', 'Sertifikasi', 'PendidikanTerakhir', 'NomorWA', 'Email', 'Alamat']);
            
            // Get all data
            $gurus = Guru::with('user')->get();
            foreach ($gurus as $guru) {
                fputcsv($file, [
                    $guru->nik,
                    $guru->nip,
                    $guru->nama_lengkap,
                    $guru->gelar_depan,
                    $guru->gelar_belakang,
                    $guru->jenis_kelamin,
                    $guru->tempat_lahir,
                    $guru->tanggal_lahir,
                    $guru->status_guru,
                    $guru->sertifikasi,
                    $guru->pendidikan_terakhir,
                    $guru->nomor_wa,
                    $guru->email ?? $guru->user?->email,
                    $guru->alamat
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");
        $header = true;
        
        DB::beginTransaction();
        try {
            $roleGuru = Role::where('role_key', 'guru')->first();
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if ($header) {
                    $header = false;
                    continue;
                }

                if (empty($data[0]) || empty($data[2])) continue; // Skip if NIK or Nama is empty

                $nik = trim($data[0]);
                $nip = trim($data[1]);
                $nama = trim($data[2]);
                $passwordAsli = Str::random(8);

                // Check if exists
                if (Guru::where('nik', $nik)->exists()) {
                    continue; // Skip existing
                }

                $user = User::create([
                    'username'     => $nik,
                    'password'     => Hash::make($passwordAsli),
                    'email'        => $nik . '@sekolah.id',
                    'nama_lengkap' => $nama,
                    'nomor_wa'     => $data[6] ?? null,
                    'role'         => 'guru',
                ]);

                if ($roleGuru) {
                    $user->roles()->attach($roleGuru->id);
                }

                Guru::create([
                    'user_id'        => $user->id,
                    'nik'            => $nik,
                    'nip'            => $nip,
                    'nama_lengkap'   => $nama,
                    'gelar_depan'    => $data[3] ?? null,
                    'gelar_belakang' => $data[4] ?? null,
                    'jenis_kelamin'  => $data[5] ?? 'L',
                    'nomor_wa'       => $data[6] ?? null,
                    'alamat'         => $data[7] ?? null,
                    'foto'           => 'default.png'
                ]);
                $count++;
            }
            fclose($handle);
            DB::commit();

            return redirect()->route('admin.guru.index')
                ->with('message', "Berhasil mengimport {$count} data guru baru. Password default adalah random, silakan direset oleh masing-masing guru.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
