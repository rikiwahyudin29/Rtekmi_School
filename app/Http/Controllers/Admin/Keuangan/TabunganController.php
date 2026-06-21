<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rekening;
use App\Models\Siswa;
use App\Models\TransaksiTabungan;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TabunganController extends Controller
{
    public function index(Request $request)
    {
        $rekening = Rekening::with(['siswa.kelas'])->orderBy('saldo', 'desc')->get();
        
        $siswaBaru = Siswa::with('kelas')
            ->whereNotIn('id', function($query) {
                $query->select('siswa_id')->from('tbl_rekening');
            })->where('status_siswa', 'Aktif')->get(['id', 'nisn', 'nama_lengkap', 'kelas_id']);

        $kelasList = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get(['id', 'nama_kelas']);

        $logGlobal = TransaksiTabungan::with(['rekening.siswa'])
                        ->orderBy('created_at', 'desc')
                        ->limit(100)
                        ->get();

        $totalUang = Rekening::where('status_rekening', 'Aktif')->sum('saldo');

        return Inertia::render('Admin/Keuangan/Tabungan/Index', [
            'rekening' => $rekening,
            'siswaBaru' => $siswaBaru,
            'logGlobal' => $logGlobal,
            'totalUang' => $totalUang,
            'kelasList' => $kelasList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:tbl_siswa,id',
            'pin' => 'required|string|min:4'
        ]);

        Rekening::create([
            'siswa_id' => $request->siswa_id,
            'pin' => Hash::make($request->pin),
            'saldo' => 0,
            'status_rekening' => 'Aktif'
        ]);

        return back()->with('message', 'Rekening baru berhasil dibuka!');
    }

    public function show($id)
    {
        $rekening = Rekening::with(['siswa.kelas'])->findOrFail($id);
        $mutasi = TransaksiTabungan::where('rekening_id', $id)->orderBy('created_at', 'desc')->get();
        
        // Asumsi ada tabel tbl_tagihan jika ada. Jika tidak, abaikan dulu.
        // Di CI4 ada tbl_tagihan. Kita coba ambil jika ada model Tagihan.
        // Untuk sekarang kosongkan tagihan dulu agar tidak error.
        $tagihan = [];
        if (DB::getSchemaBuilder()->hasTable('tbl_tagihan')) {
            $tagihan = DB::table('tbl_tagihan')
                ->where('id_siswa', $rekening->siswa_id)
                ->where('status_bayar', '!=', 'LUNAS')
                ->get();
        }

        $rekeningLain = Rekening::with('siswa')
            ->where('status_rekening', 'Aktif')
            ->where('id', '!=', $id)
            ->get();

        return Inertia::render('Admin/Keuangan/Tabungan/Detail', [
            'rekening' => $rekening,
            'mutasi' => $mutasi,
            'tagihan' => $tagihan,
            'rekeningLain' => $rekeningLain
        ]);
    }

    public function prosesTransaksi(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:tbl_rekening,id',
            'jenis_transaksi' => 'required|in:Setor,Tarik,Transfer,Bayar_Sekolah',
            'nominal' => 'required|numeric|min:1'
        ]);

        $rekening = Rekening::findOrFail($request->rekening_id);
        
        if ($rekening->status_rekening == 'Blokir') {
            return back()->withErrors(['error' => 'Rekening sedang diblokir!']);
        }

        $nominal = $request->nominal;
        $jenis = $request->jenis_transaksi;
        $keterangan = $request->keterangan;
        $id_tagihan = $request->id_tagihan;

        $saldo_lama = $rekening->saldo;
        $saldo_baru = $saldo_lama;

        DB::beginTransaction();
        try {
            if ($jenis == 'Setor') {
                $saldo_baru += $nominal;
                if (empty($keterangan)) $keterangan = "Setoran Tunai";
            } elseif ($jenis == 'Tarik') {
                if ($saldo_lama < $nominal) {
                    return back()->withErrors(['error' => 'Saldo tidak mencukupi untuk penarikan!']);
                }
                $saldo_baru -= $nominal;
                if (empty($keterangan)) $keterangan = "Penarikan Tunai";
            } elseif ($jenis == 'Bayar_Sekolah') {
                if ($saldo_lama < $nominal) {
                    return back()->withErrors(['error' => 'Saldo tabungan tidak mencukupi!']);
                }
                $saldo_baru -= $nominal;
                
                if (!empty($id_tagihan) && DB::getSchemaBuilder()->hasTable('tbl_tagihan')) {
                    $cek_tagihan = DB::table('tbl_tagihan')->where('id', $id_tagihan)->first();
                    if ($cek_tagihan) {
                        $terbayar_baru = $cek_tagihan->nominal_terbayar + $nominal;
                        $status_bayar = ($terbayar_baru >= $cek_tagihan->nominal_tagihan) ? 'LUNAS' : 'CICIL';

                        DB::table('tbl_tagihan')->where('id', $id_tagihan)->update([
                            'nominal_terbayar' => $terbayar_baru,
                            'status_bayar'     => $status_bayar
                        ]);
                        $keterangan = "Bayar Tagihan Sekolah via Tabungan";
                    }
                }
            } elseif ($jenis == 'Transfer') {
                $id_tujuan = $request->rekening_tujuan;
                if (empty($id_tujuan) || $saldo_lama < $nominal) {
                    return back()->withErrors(['error' => 'Saldo tidak cukup atau rekening tujuan kosong!']);
                }

                $rek_tujuan = Rekening::with('siswa')->findOrFail($id_tujuan);
                if ($rek_tujuan->status_rekening == 'Blokir') {
                    return back()->withErrors(['error' => 'Rekening tujuan diblokir!']);
                }

                $saldo_baru -= $nominal;
                $saldo_tujuan_baru = $rek_tujuan->saldo + $nominal;
                $rek_tujuan->update(['saldo' => $saldo_tujuan_baru]);

                $nama_pengirim = $rekening->siswa->nama_lengkap ?? 'Siswa';
                $nama_penerima = $rek_tujuan->siswa->nama_lengkap ?? 'Siswa';

                // Log untuk penerima
                TransaksiTabungan::create([
                    'rekening_id' => $id_tujuan,
                    'jenis_transaksi' => 'Transfer_Masuk',
                    'nominal' => $nominal,
                    'saldo_setelah_transaksi' => $saldo_tujuan_baru,
                    'keterangan' => 'Terima Dana dari ' . $nama_pengirim . ' - ' . $keterangan,
                    'referensi_tujuan' => $rekening->id,
                    'petugas_id' => auth()->id() ?? 1
                ]);

                $jenis = 'Transfer_Keluar';
                $keterangan = 'Kirim Dana ke ' . $nama_penerima . ' - ' . $keterangan;
            }

            // Update pengirim
            $rekening->update(['saldo' => $saldo_baru]);

            TransaksiTabungan::create([
                'rekening_id' => $rekening->id,
                'jenis_transaksi' => $jenis,
                'nominal' => $nominal,
                'saldo_setelah_transaksi' => $saldo_baru,
                'keterangan' => $keterangan,
                'petugas_id' => auth()->id() ?? 1
            ]);

            DB::commit();
            return back()->with('message', 'Transaksi berhasil diproses!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
        }
    }
}
