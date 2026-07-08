<?php

namespace App\Http\Controllers\Admin\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Models\SuratTemplate;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuratKeluarController extends Controller
{
    public function index()
    {
        $surat = SuratKeluar::with('siswa')->orderBy('id', 'desc')->paginate(10);
        $templates = SuratTemplate::all();
        $kelas = Kelas::all();
        $siswa = Siswa::with('kelas')->get();

        return Inertia::render('Admin/Surat/Keluar/Index', [
            'surat' => $surat,
            'templates' => $templates,
            'kelas' => $kelas,
            'siswa' => $siswa
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:tbl_surat_template,id',
            'siswa_id' => 'required|exists:tbl_siswa,id'
        ]);

        $template = SuratTemplate::find($request->template_id);
        $siswa = Siswa::with('kelas')->find($request->siswa_id);

        $bulanIni = date('Y-m');
        $count = SuratKeluar::where('tgl_surat', 'like', $bulanIni . '%')->count();
        $noUrut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $nomorSurat = str_replace(
            ['{NO}', '{THN}', '{BLN}'],
            [$noUrut, date('Y'), date('m')],
            $template->format_nomor
        );

        $isiFinal = str_replace(
            ['{NAMA}', '{NIS}', '{KELAS}', '{ALAMAT}', '{TAHUN_AJARAN}', '{HARI_INI}'],
            [
                $siswa->nama_lengkap,
                $siswa->nis,
                $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                $siswa->alamat ?? 'Alamat Siswa',
                date('Y') . '/' . (date('Y') + 1),
                \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y')
            ],
            $template->isi_html
        );

        $token = md5(uniqid(rand(), true));

        SuratKeluar::create([
            'template_id' => $template->id,
            'no_surat' => $nomorSurat,
            'siswa_id' => $siswa->id,
            'perihal' => $template->nama_template,
            'isi_final' => $isiFinal,
            'tgl_surat' => date('Y-m-d'),
            'ttd_oleh' => Auth::id() ?? 1,
            'token_validasi' => $token,
            'status' => 'Disetujui'
        ]);

        return redirect()->back()->with('success', 'Surat berhasil dibuat dengan Nomor: ' . $nomorSurat);
    }

    public function destroy($id)
    {
        SuratKeluar::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Arsip dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tbl_surat_keluar,id'
        ]);

        SuratKeluar::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', count($request->ids) . ' Arsip berhasil dihapus massal!');
    }

    public function cetak($id)
    {
        $surat = SuratKeluar::with(['siswa', 'siswa.kelas'])->findOrFail($id);

        if (strpos($surat->perihal, 'Sertifikat Praktik Kerja Lapangan') !== false) {
            $nilai = \Illuminate\Support\Facades\DB::table('tbl_pkl_nilai')->where('token_sertifikat', $surat->token_validasi)->first();
            if($nilai) {
                $pklController = new \App\Http\Controllers\Guru\PklController();
                return $pklController->cetakSertifikat($nilai->pkl_id);
            }
        }

        if (strpos($surat->perihal, 'Surat Keterangan Lulus') !== false) {
            $kelulusanController = new \App\Http\Controllers\Admin\KelulusanController();
            return $kelulusanController->cetakSkl($surat->siswa_id);
        }
        if (strpos($surat->perihal, 'Transkrip Nilai') !== false) {
            $kelulusanController = new \App\Http\Controllers\Admin\KelulusanController();
            return $kelulusanController->cetakTranskrip($surat->siswa_id);
        }

        if (strpos($surat->perihal, 'Sertifikat Ekstrakurikuler') !== false) {
            $nilai = \Illuminate\Support\Facades\DB::table('tbl_ekskul_nilai')->where('token_sertifikat', $surat->token_validasi)->first();
            if($nilai) {
                $ekskulController = new \App\Http\Controllers\Guru\EkskulController();
                return $ekskulController->cetakSertifikat($nilai->id);
            }
        }

        $sekolah = Sekolah::find(1);
        $qrCodeUrl = route('surat.verifikasi', $surat->token_validasi);
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qr_link = 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeUrl));
        return view('surat.cetak', compact('surat', 'sekolah', 'qr_link'));
    }

    public function cetakPublic($token)
    {
        $surat = SuratKeluar::with(['siswa', 'siswa.kelas'])->where('token_validasi', $token)->firstOrFail();
        
        if (strpos($surat->perihal, 'Sertifikat Praktik Kerja Lapangan') !== false) {
            $nilai = \Illuminate\Support\Facades\DB::table('tbl_pkl_nilai')->where('token_sertifikat', $surat->token_validasi)->first();
            if($nilai) {
                $pklController = new \App\Http\Controllers\Guru\PklController();
                return $pklController->cetakSertifikat($nilai->pkl_id);
            }
        }

        if ($surat->perihal == 'Surat Keterangan Lulus' || strpos($surat->perihal, 'Surat Keterangan Lulus') !== false) {
            $kelulusanController = new \App\Http\Controllers\Admin\KelulusanController();
            return $kelulusanController->cetakSkl($surat->siswa_id);
        } 
        
        if ($surat->perihal == 'Transkrip Nilai' || strpos($surat->perihal, 'Transkrip Nilai') !== false) {
            $kelulusanController = new \App\Http\Controllers\Admin\KelulusanController();
            return $kelulusanController->cetakTranskrip($surat->siswa_id);
        }

        if (strpos($surat->perihal, 'Sertifikat Ekstrakurikuler') !== false) {
            $nilai = \Illuminate\Support\Facades\DB::table('tbl_ekskul_nilai')->where('token_sertifikat', $surat->token_validasi)->first();
            if($nilai) {
                $ekskulController = new \App\Http\Controllers\Guru\EkskulController();
                return $ekskulController->cetakSertifikat($nilai->id);
            }
        }

        $sekolah = Sekolah::find(1);
        $qrCodeUrl = route('surat.verifikasi', $surat->token_validasi);
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qr_link = 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeUrl));
        return view('surat.cetak', compact('surat', 'sekolah', 'qr_link'));
    }

    public function verifikasi($token)
    {
        $surat = SuratKeluar::with(['siswa', 'siswa.kelas'])->where('token_validasi', $token)->first();
        $sekolah = Sekolah::find(1);

        if ($surat) {
            $surat->nama_kepsek = $sekolah->nama_kepsek;
            $surat->nip = $sekolah->nip_kepsek;

            return Inertia::render('Public/Verifikasi', [
                'surat' => $surat,
                'sekolah' => $sekolah,
                'isValid' => true
            ]);
        } else {
            return Inertia::render('Public/Verifikasi', [
                'surat' => null,
                'sekolah' => $sekolah,
                'isValid' => false
            ]);
        }
    }
}
