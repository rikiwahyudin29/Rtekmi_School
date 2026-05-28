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
        $sekolah = Sekolah::find(1);
        $qr_link = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('surat.verifikasi', $surat->token_validasi));
        return view('surat.cetak', compact('surat', 'sekolah', 'qr_link'));
    }

    public function cetakPublic($token)
    {
        $surat = SuratKeluar::with(['siswa', 'siswa.kelas'])->where('token_validasi', $token)->firstOrFail();
        $sekolah = Sekolah::find(1);
        $qr_link = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('surat.verifikasi', $surat->token_validasi));
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
