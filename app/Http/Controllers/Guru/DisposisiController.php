<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DisposisiController extends Controller
{
    private function getGuruId()
    {
        $guru = DB::table('tbl_guru')
            ->where('id_user', Auth::id())
            ->orWhere('user_id', Auth::id())
            ->first();

        return $guru ? $guru->id : Auth::id();
    }

    /**
     * Kotak Masuk Disposisi Guru
     */
    public function index(Request $request)
    {
        $id_guru = $this->getGuruId();
        $status = $request->get('status', 'semua');

        $query = DB::table('tbl_disposisi')
            ->select(
                'tbl_disposisi.*',
                'tbl_surat_masuk.nomor_surat',
                'tbl_surat_masuk.tanggal_surat',
                'tbl_surat_masuk.pengirim',
                'tbl_surat_masuk.perihal',
                'tbl_surat_masuk.file_scan'
            )
            ->join('tbl_surat_masuk', 'tbl_surat_masuk.id', '=', 'tbl_disposisi.id_surat')
            ->where('tbl_disposisi.id_penerima', $id_guru);

        if ($status === 'belum_dibaca') {
            $query->where('tbl_disposisi.is_read', 0);
        } elseif ($status === 'sudah_dibaca') {
            $query->where('tbl_disposisi.is_read', 1);
        }

        $disposisi = $query->orderBy('tbl_disposisi.tanggal_disposisi', 'DESC')->get();

        // Hitung unread
        $unread_count = DB::table('tbl_disposisi')
            ->where('id_penerima', $id_guru)
            ->where('is_read', 0)
            ->count();

        return Inertia::render('Guru/Disposisi/Index', [
            'disposisi'    => $disposisi,
            'unread_count' => $unread_count,
            'status'       => $status,
        ]);
    }

    /**
     * Tandai disposisi sebagai sudah dibaca
     */
    public function baca($id)
    {
        $id_guru = $this->getGuruId();

        DB::table('tbl_disposisi')
            ->where('id', $id)
            ->where('id_penerima', $id_guru)
            ->update(['is_read' => 1]);

        return back()->with('message', 'Disposisi ditandai sudah dibaca.');
    }

    /**
     * Tandai semua disposisi sebagai sudah dibaca
     */
    public function bacaSemua()
    {
        $id_guru = $this->getGuruId();

        DB::table('tbl_disposisi')
            ->where('id_penerima', $id_guru)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return back()->with('message', 'Semua disposisi ditandai sudah dibaca.');
    }
}
