<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profil = null;
        
        if ($user->role === 'guru') {
            $profil = \App\Models\Guru::where('user_id', $user->id)->first();
        } elseif ($user->role === 'siswa') {
            $profil = \App\Models\Siswa::where('user_id', $user->id)->first();
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'profil' => $profil,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $rules = [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', \Illuminate\Validation\Rule::unique('tbl_users')->ignore($user->id)],
            'nomor_wa' => ['nullable', 'string', 'max:20'],
            'foto' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ];

        if ($user->role === 'guru') {
            $rules = array_merge($rules, [
                'nip' => ['nullable', 'string', 'max:30'],
                'nik' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('tbl_users', 'username')->ignore($user->id)],
                'gelar_depan' => ['nullable', 'string', 'max:50'],
                'gelar_belakang' => ['nullable', 'string', 'max:50'],
                'jenis_kelamin' => ['nullable', 'in:L,P'],
                'tempat_lahir' => ['nullable', 'string', 'max:50'],
                'tanggal_lahir' => ['nullable', 'date'],
                'alamat' => ['nullable', 'string'],
                'nuptk' => ['nullable', 'string', 'max:30'],
                'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
                'sertifikasi' => ['nullable', 'string', 'max:100'],
                'status_guru' => ['nullable', 'string', 'in:PNS,GTT,GTY,HONORER'],
                'ibu_kandung' => ['nullable', 'string', 'max:100'],
                'status_kepegawaian' => ['nullable', 'string', 'max:50'],
            ]);
        } elseif ($user->role === 'siswa') {
            $rules = array_merge($rules, [
                'nisn' => ['nullable', 'string', 'max:20'],
                'nis' => ['nullable', 'string', 'max:20'],
                'jenis_kelamin' => ['nullable', 'in:L,P'],
                'tempat_lahir' => ['nullable', 'string', 'max:50'],
                'tanggal_lahir' => ['nullable', 'date'],
                'alamat' => ['nullable', 'string'],
                'nama_ayah' => ['nullable', 'string', 'max:100'],
                'nama_ibu' => ['nullable', 'string', 'max:100'],
                'agama' => ['nullable', 'string', 'max:20'],
                'status_keluarga' => ['nullable', 'string', 'max:50'],
                'anak_ke' => ['nullable', 'integer'],
                'sekolah_asal' => ['nullable', 'string', 'max:100'],
                'diterima_kelas' => ['nullable', 'string', 'max:20'],
                'tanggal_diterima' => ['nullable', 'date'],
                'nama_wali' => ['nullable', 'string', 'max:100'],
                'pekerjaan_wali' => ['nullable', 'string', 'max:50'],
                'no_hp_ortu' => ['nullable', 'string', 'max:20'],
                'pekerjaan_ayah' => ['nullable', 'string', 'max:50'],
                'pekerjaan_ibu' => ['nullable', 'string', 'max:50'],
            ]);
        }

        $validated = $request->validate($rules);

        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->email = $validated['email'];
        if(isset($validated['nomor_wa'])) $user->nomor_wa = $validated['nomor_wa'];

        if ($user->role === 'guru' && isset($validated['nik'])) {
            $user->username = $validated['nik'];
        }

        $user->save();

        // Handle Photo Upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Using public_path to save it in public/uploads/guru or public/uploads/siswa
            $folder = $user->role === 'guru' ? 'uploads/guru' : 'uploads/siswa';
            $file->move(public_path($folder), $filename);
            $fotoPath = $filename;
        }

        // Update Profil Table
        if ($user->role === 'guru') {
            $guru = \App\Models\Guru::where('user_id', $user->id)->first();
            if ($guru) {
                $guru->nama_lengkap = $validated['nama_lengkap'];
                $guru->email = $validated['email'];
                $guru->nip = array_key_exists('nip', $validated) ? $validated['nip'] : $guru->nip;
                $guru->nik = array_key_exists('nik', $validated) ? $validated['nik'] : $guru->nik;
                $guru->gelar_depan = $validated['gelar_depan'] ?? $guru->gelar_depan;
                $guru->gelar_belakang = $validated['gelar_belakang'] ?? $guru->gelar_belakang;
                $guru->jenis_kelamin = $validated['jenis_kelamin'] ?? $guru->jenis_kelamin;
                $guru->tempat_lahir = $validated['tempat_lahir'] ?? $guru->tempat_lahir;
                $guru->tanggal_lahir = $validated['tanggal_lahir'] ?? $guru->tanggal_lahir;
                $guru->alamat = $validated['alamat'] ?? $guru->alamat;
                $guru->nuptk = $validated['nuptk'] ?? $guru->nuptk;
                $guru->pendidikan_terakhir = $validated['pendidikan_terakhir'] ?? $guru->pendidikan_terakhir;
                $guru->sertifikasi = $validated['sertifikasi'] ?? $guru->sertifikasi;
                $guru->status_guru = $validated['status_guru'] ?? $guru->status_guru;
                $guru->ibu_kandung = $validated['ibu_kandung'] ?? $guru->ibu_kandung;
                $guru->status_kepegawaian = $validated['status_kepegawaian'] ?? $guru->status_kepegawaian;
                if ($fotoPath) {
                    $guru->foto = $fotoPath;
                }
                $guru->save();
            }
        } elseif ($user->role === 'siswa') {
            $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
            if ($siswa) {
                $siswa->nama_lengkap = $validated['nama_lengkap'];
                $siswa->email_siswa = $validated['email'];
                $siswa->nisn = $validated['nisn'] ?? $siswa->nisn;
                $siswa->nis = $validated['nis'] ?? $siswa->nis;
                $siswa->jenis_kelamin = $validated['jenis_kelamin'] ?? $siswa->jenis_kelamin;
                $siswa->tempat_lahir = $validated['tempat_lahir'] ?? $siswa->tempat_lahir;
                $siswa->tanggal_lahir = $validated['tanggal_lahir'] ?? $siswa->tanggal_lahir;
                $siswa->alamat = $validated['alamat'] ?? $siswa->alamat;
                $siswa->nama_ayah = $validated['nama_ayah'] ?? $siswa->nama_ayah;
                $siswa->nama_ibu = $validated['nama_ibu'] ?? $siswa->nama_ibu;
                $siswa->agama = $validated['agama'] ?? $siswa->agama;
                $siswa->status_keluarga = $validated['status_keluarga'] ?? $siswa->status_keluarga;
                $siswa->anak_ke = $validated['anak_ke'] ?? $siswa->anak_ke;
                $siswa->sekolah_asal = $validated['sekolah_asal'] ?? $siswa->sekolah_asal;
                $siswa->diterima_kelas = $validated['diterima_kelas'] ?? $siswa->diterima_kelas;
                $siswa->tanggal_diterima = $validated['tanggal_diterima'] ?? $siswa->tanggal_diterima;
                $siswa->nama_wali = $validated['nama_wali'] ?? $siswa->nama_wali;
                $siswa->pekerjaan_wali = $validated['pekerjaan_wali'] ?? $siswa->pekerjaan_wali;
                $siswa->no_hp_ortu = $validated['no_hp_ortu'] ?? $siswa->no_hp_ortu;
                $siswa->pekerjaan_ayah = $validated['pekerjaan_ayah'] ?? $siswa->pekerjaan_ayah;
                $siswa->pekerjaan_ibu = $validated['pekerjaan_ibu'] ?? $siswa->pekerjaan_ibu;
                if ($fotoPath) {
                    $siswa->foto = $fotoPath;
                }
                $siswa->save();
            }
        }

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
