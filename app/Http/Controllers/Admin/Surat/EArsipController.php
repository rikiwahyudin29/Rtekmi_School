<?php

namespace App\Http\Controllers\Admin\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EArsipController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $tgl_awal = $request->get('tgl_awal');
        $tgl_akhir = $request->get('tgl_akhir');
        $jenis = $request->get('jenis', 'Semua');

        $data_arsip = [];

        if ($jenis == 'Semua' || $jenis == 'Masuk') {
            $query = SuratMasuk::query();
            
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where('nomor_surat', 'like', "%{$keyword}%")
                      ->orWhere('perihal', 'like', "%{$keyword}%")
                      ->orWhere('pengirim', 'like', "%{$keyword}%");
                });
            }
            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $query->whereBetween('tanggal_surat', [$tgl_awal, $tgl_akhir]);
            }

            $masuk = $query->get();
            foreach ($masuk as $m) {
                $data_arsip[] = [
                    'id'          => 'm_'.$m->id,
                    'jenis_surat' => 'Surat Masuk',
                    'nomor_surat' => $m->nomor_surat,
                    'tanggal'     => $m->tanggal_surat,
                    'pihak'       => $m->pengirim,
                    'perihal'     => $m->perihal,
                    'file'        => $m->file_scan ? asset('uploads/surat_masuk/' . $m->file_scan) : null,
                    'warna_badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'is_cetak'    => false
                ];
            }
        }

        if ($jenis == 'Semua' || $jenis == 'Keluar') {
            $query = SuratKeluar::with('siswa');
            
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where('no_surat', 'like', "%{$keyword}%")
                      ->orWhere('perihal', 'like', "%{$keyword}%")
                      ->orWhereHas('siswa', function($sq) use ($keyword) {
                          $sq->where('nama_lengkap', 'like', "%{$keyword}%");
                      });
                });
            }
            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $query->whereBetween('tgl_surat', [$tgl_awal, $tgl_akhir]);
            }

            $keluar = $query->get();
            foreach ($keluar as $k) {
                $data_arsip[] = [
                    'id'          => 'k_'.$k->id,
                    'jenis_surat' => 'Surat Keluar',
                    'nomor_surat' => $k->no_surat,
                    'tanggal'     => $k->tgl_surat,
                    'pihak'       => $k->siswa ? $k->siswa->nama_lengkap : 'Umum',
                    'perihal'     => $k->perihal,
                    'file'        => route('surat.cetak', $k->id),
                    'warna_badge' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'is_cetak'    => true
                ];
            }
        }

        usort($data_arsip, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });

        // Pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $pagedData = array_slice($data_arsip, $offset, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, count($data_arsip), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return Inertia::render('Admin/Surat/Arsip/Index', [
            'arsip' => $paginator,
            'filter' => [
                'keyword'   => $keyword,
                'tgl_awal'  => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'jenis'     => $jenis
            ]
        ]);
    }
}
