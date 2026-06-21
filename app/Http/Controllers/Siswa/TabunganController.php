<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rekening;
use App\Models\Siswa;
use App\Models\TransaksiTabungan;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TabunganController extends Controller
{
    public function index()
    {
        // Ambil user auth, kita asumsikan user_id memiliki relasi dengan tabel Siswa.
        // Berdasarkan Siswa.php, ada `public function user(): BelongsTo` yang berarti 'user_id' di tabel siswa.
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return back()->withErrors(['error' => 'Data siswa tidak ditemukan untuk akun Anda.']);
        }

        $rekening = Rekening::where('siswa_id', $siswa->id)->first();

        $mutasi = [];
        $tagihan = [];

        if ($rekening) {
            $mutasi = TransaksiTabungan::where('rekening_id', $rekening->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
            
            if (DB::getSchemaBuilder()->hasTable('tbl_tagihan')) {
                $tagihan = DB::table('tbl_tagihan')
                    ->where('id_siswa', $siswa->id)
                    ->where('status_bayar', '!=', 'LUNAS')
                    ->get();
            }
        }

        $rekeningLain = Rekening::with('siswa')
            ->where('status_rekening', 'Aktif')
            ->where('siswa_id', '!=', $siswa->id)
            ->get();

        return Inertia::render('Siswa/Tabungan/Index', [
            'siswa' => $siswa,
            'rekening' => $rekening,
            'mutasi' => $mutasi,
            'tagihan' => $tagihan,
            'rekeningLain' => $rekeningLain
        ]);
    }

    public function prosesTransfer(Request $request)
    {
        $request->validate([
            'id_tujuan' => 'required|exists:tbl_rekening,id',
            'nominal' => 'required|numeric|min:1',
            'pin' => 'required'
        ]);

        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();
        $rek_saya = Rekening::where('siswa_id', $siswa->id)->first();

        if (!$rek_saya || $rek_saya->status_rekening == 'Blokir') {
            return back()->withErrors(['error' => 'Rekening Anda tidak valid atau terblokir.']);
        }

        if (!Hash::check($request->pin, $rek_saya->pin)) {
            return back()->withErrors(['error' => 'PIN Tabungan SALAH!']);
        }

        $nominal = $request->nominal;
        if ($rek_saya->saldo < $nominal) {
            return back()->withErrors(['error' => 'Saldo Anda tidak mencukupi!']);
        }

        $rek_tujuan = Rekening::with('siswa')->findOrFail($request->id_tujuan);
        if ($rek_tujuan->status_rekening == 'Blokir') {
            return back()->withErrors(['error' => 'Rekening tujuan terblokir!']);
        }

        $pesan = $request->keterangan ?? 'Transfer via Mobile Banking Siswa';

        DB::beginTransaction();
        try {
            // Potong Saldo Pengirim
            $saldo_saya_baru = $rek_saya->saldo - $nominal;
            $rek_saya->update(['saldo' => $saldo_saya_baru]);
            
            TransaksiTabungan::create([
                'rekening_id' => $rek_saya->id,
                'jenis_transaksi' => 'Transfer_Keluar',
                'nominal' => $nominal,
                'saldo_setelah_transaksi' => $saldo_saya_baru,
                'keterangan' => 'Kirim ke ' . ($rek_tujuan->siswa->nama_lengkap ?? 'Siswa') . ' - ' . $pesan,
                'petugas_id' => null
            ]);

            // Tambah Saldo Tujuan
            $saldo_tujuan_baru = $rek_tujuan->saldo + $nominal;
            $rek_tujuan->update(['saldo' => $saldo_tujuan_baru]);

            TransaksiTabungan::create([
                'rekening_id' => $rek_tujuan->id,
                'jenis_transaksi' => 'Transfer_Masuk',
                'nominal' => $nominal,
                'saldo_setelah_transaksi' => $saldo_tujuan_baru,
                'keterangan' => 'Terima dari ' . ($siswa->nama_lengkap ?? 'Siswa') . ' - ' . $pesan,
                'referensi_tujuan' => $rek_saya->id,
                'petugas_id' => null
            ]);

            DB::commit();
            return back()->with('message', 'Transfer Berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Transaksi gagal: ' . $e->getMessage()]);
        }
    }
}
