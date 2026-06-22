<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SiswaPelanggaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class KedisiplinanController extends Controller
{
    public function index()
    {
        $siswa_id = Auth::user()->siswa->id ?? 0;

        // Ambil riwayat pelanggaran dengan relasi master pelanggaran dan pelapor (guru/admin)
        $riwayat_pelanggaran = SiswaPelanggaran::with(['pelanggaran', 'pelapor'])
            ->where('siswa_id', $siswa_id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Ambil pengaturan SP
        $set_sp = \App\Models\SetSp::first();
        $batas_sp1 = $set_sp ? $set_sp->sp_1 : 50;
        $batas_sp2 = $set_sp ? $set_sp->sp_2 : 30;
        $batas_sp3 = $set_sp ? $set_sp->sp_3 : 0;

        // Hitung total poin pelanggaran
        $total_poin_pelanggaran = 0;
        foreach ($riwayat_pelanggaran as $p) {
            // Asumsikan semua data pelanggaran yang ada di tabel ini valid dan harus dihitung
            $total_poin_pelanggaran += $p->pelanggaran->poin ?? 0;
        }

        // Hitung sisa poin
        $sisa_poin = 100 - $total_poin_pelanggaran;

        // Tentukan Status
        $status_sp = 'Aman';
        if ($sisa_poin <= $batas_sp3) {
            $status_sp = 'SP 3 (Dikeluarkan)';
        } elseif ($sisa_poin <= $batas_sp2) {
            $status_sp = 'SP 2';
        } elseif ($sisa_poin <= $batas_sp1) {
            $status_sp = 'SP 1';
        }

        return Inertia::render('Siswa/Kedisiplinan/Index', [
            'riwayat_pelanggaran' => $riwayat_pelanggaran,
            'sisa_poin' => $sisa_poin,
            'status_sp' => $status_sp,
            'set_sp' => $set_sp
        ]);
    }
}
