<?php

namespace App\Http\Controllers\Admin\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\Guru;
use App\Models\Disposisi;
use App\Services\WaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    protected $wa;

    public function __construct(WaService $wa)
    {
        $this->wa = $wa;
    }

    public function index()
    {
        $surat = SuratMasuk::orderBy('tanggal_diterima', 'desc')->paginate(10);
        $staff = Guru::orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Surat/Masuk/Index', [
            'surat' => $surat,
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'required|date',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'file_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $namaFile = null;
        if ($request->hasFile('file_scan')) {
            $file = $request->file('file_scan');
            $namaFile = $file->hashName();
            // Store in public/uploads/surat_masuk
            $file->move(public_path('uploads/surat_masuk'), $namaFile);
        }

        SuratMasuk::create([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_diterima' => $request->tanggal_diterima,
            'pengirim' => $request->pengirim,
            'perihal' => $request->perihal,
            'file_scan' => $namaFile,
            'status_disposisi' => 'Belum Disposisi'
        ]);

        return redirect()->back()->with('success', 'Surat Masuk berhasil disimpan!');
    }

    public function disposisi(Request $request)
    {
        $request->validate([
            'id_surat' => 'required|exists:tbl_surat_masuk,id',
            'id_penerima' => 'required|exists:tbl_guru,id',
            'instruksi' => 'required|string'
        ]);

        Disposisi::create([
            'id_surat' => $request->id_surat,
            'id_penerima' => $request->id_penerima,
            'instruksi' => $request->instruksi
        ]);

        $surat = SuratMasuk::find($request->id_surat);
        $surat->update(['status_disposisi' => 'Sudah Disposisi']);

        $penerima = Guru::find($request->id_penerima);

        if ($penerima && !empty($penerima->no_hp)) {
            $pesan = "📬 *DISPOSISI SURAT BARU*\n\n";
            $pesan .= "Yth. Bapak/Ibu *" . $penerima->nama_lengkap . "*,\n";
            $pesan .= "Terdapat disposisi surat masuk yang perlu segera ditindaklanjuti:\n\n";
            $pesan .= "🔹 *Dari:* " . $surat->pengirim . "\n";
            $pesan .= "🔹 *No Surat:* " . $surat->nomor_surat . "\n";
            $pesan .= "🔹 *Perihal:* " . $surat->perihal . "\n";
            $pesan .= "🔹 *Instruksi:* _" . $request->instruksi . "_\n\n";
            $pesan .= "Silakan login ke SIAKAD untuk mengunduh lampiran/scan surat.\nTerima kasih.";

            $this->wa->kirim($penerima->no_hp, $pesan);
        }

        return redirect()->back()->with('success', 'Surat berhasil didisposisikan dan Notif WA telah dikirim ke Staff!');
    }

    public function destroy($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        if (!empty($surat->file_scan) && file_exists(public_path('uploads/surat_masuk/' . $surat->file_scan))) {
            unlink(public_path('uploads/surat_masuk/' . $surat->file_scan));
        }

        Disposisi::where('id_surat', $id)->delete();
        $surat->delete();

        return redirect()->back()->with('success', 'Data Surat Masuk beserta file scan berhasil dihapus!');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tbl_surat_masuk,id'
        ]);

        $surats = SuratMasuk::whereIn('id', $request->ids)->get();

        foreach ($surats as $surat) {
            if (!empty($surat->file_scan) && file_exists(public_path('uploads/surat_masuk/' . $surat->file_scan))) {
                unlink(public_path('uploads/surat_masuk/' . $surat->file_scan));
            }
        }

        Disposisi::whereIn('id_surat', $request->ids)->delete();
        SuratMasuk::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' Data Surat Masuk berhasil dihapus massal!');
    }
}
