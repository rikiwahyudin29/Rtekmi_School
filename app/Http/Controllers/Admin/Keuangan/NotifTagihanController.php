<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\LogKeuangan;
use App\Services\WaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotifTagihanController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Tagihan/Notif', [
            'kelas' => $kelas
        ]);
    }

    public function kirimMassal(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|integer'
        ]);

        $id_kelas = $request->id_kelas;
        
        $sekolah = Sekolah::find(1);
        $namaSekolah = $sekolah->nama_sekolah ?? 'Sekolah';

        $siswa = Siswa::with('kelas')->where('id_kelas', $id_kelas)->get();

        $berhasil = 0;
        $gagal = 0;

        $wa = new WaService();

        foreach ($siswa as $s) {
            $tagihan = Tagihan::with(['jenisBayar.posBayar'])
                ->where('id_siswa', $s->id)
                ->whereIn('status_bayar', ['BELUM', 'CICIL'])
                ->get();

            if ($tagihan->count() > 0 && !empty($s->no_hp_ortu)) {
                
                $pesan = "⚠️ *INFO TAGIHAN SEKOLAH* ⚠️\n\n";
                $pesan .= "Yth. Bapak/Ibu Orang Tua/Wali dari:\n";
                $pesan .= "👤 Nama  : *{$s->nama_lengkap}*\n";
                $pesan .= "🔖 NIS   : {$s->nis}\n";
                $pesan .= "🏫 Kelas : " . ($s->kelas->nama_kelas ?? '-') . "\n\n";
                
                $pesan .= "Bersama ini kami informasikan rincian tagihan administrasi sekolah yang *belum lunas*:\n\n";
                
                $total_tunggakan = 0;
                $no = 1;

                foreach ($tagihan as $t) {
                    $sisa = $t->nominal_tagihan - $t->nominal_terbayar; 
                    $nama_tagihan = $t->jenisBayar->posBayar->nama_pos ?? 'Tagihan'; 
                    
                    if (!empty($t->keterangan)) {
                        $nama_tagihan .= " " . $t->keterangan;
                    } elseif (!empty($t->bulan_ke) && $t->bulan_ke > 0) {
                        $nama_tagihan .= " (Bulan ke-" . $t->bulan_ke . ")";
                    }

                    $pesan .= "{$no}. {$nama_tagihan}\n";
                    $pesan .= "   Sisa Tagihan: *Rp " . number_format($sisa, 0, ',', '.') . "*\n";
                    
                    $total_tunggakan += $sisa;
                    $no++;
                }

                $pesan .= "-----------------------------------\n";
                $pesan .= "🔴 *TOTAL TUNGGAKAN : Rp " . number_format($total_tunggakan, 0, ',', '.') . "*\n\n";
                
                $pesan .= "Mohon berkenan untuk segera melakukan pelunasan tagihan tersebut. Jika Bapak/Ibu sudah melakukan pembayaran, mohon abaikan pesan ini atau konfirmasi dengan mengirimkan bukti transfer.\n\n";
                $pesan .= "Terima kasih atas kerja samanya.\n";
                $pesan .= "_Keuangan {$namaSekolah}_";

                $kirim = $wa->kirim($s->no_hp_ortu, $pesan);
                
                if ($kirim) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            } else {
                $gagal++;
            }
        }

        LogKeuangan::create([
            'aksi' => "Blast Tagihan WA Kelas " . ($siswa->first()->kelas->nama_kelas ?? 'Unknown') . " (Berhasil: $berhasil, Gagal: $gagal)",
            'user_id' => auth()->id(),
            'nama_user' => auth()->user()->nama_lengkap ?? auth()->user()->username,
            'role' => auth()->user()->role,
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('message', "Proses Selesai! Pesan WA berhasil dikirim ke $berhasil Orang Tua. (Lewati/Lunas/Gagal: $gagal siswa)");
    }
}
