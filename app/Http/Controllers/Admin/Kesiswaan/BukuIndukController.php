<?php

namespace App\Http\Controllers\Admin\Kesiswaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuIndukController extends Controller
{
    // 1. TAMPILAN DAFTAR SISWA (FILTER PER KELAS)
    public function index(Request $request)
    {
        $filter_kelas = $request->input('kelas_id');
        $search = $request->input('search');
        $per_page = $request->input('per_page', 10);
        
        $kelas = DB::table('tbl_kelas')->orderBy('nama_kelas', 'ASC')->get();

        $builder = DB::table('tbl_siswa')
            ->select('tbl_siswa.id', 'tbl_siswa.nis', 'tbl_siswa.nisn', 'tbl_siswa.nama_lengkap', 'tbl_siswa.status_siswa', 'tbl_kelas.nama_kelas', 'tbl_siswa_detail.id as detail_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->leftJoin('tbl_siswa_detail', 'tbl_siswa_detail.siswa_id', '=', 'tbl_siswa.id');

        if (!empty($search)) {
            $builder->where(function($q) use ($search) {
                $q->where('tbl_siswa.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('tbl_siswa.nis', 'like', "%{$search}%");
            });
        }

        if (!empty($filter_kelas)) {
            $builder->where('tbl_siswa.kelas_id', $filter_kelas);
        }

        $siswa = $builder->orderBy('tbl_kelas.nama_kelas', 'ASC')->orderBy('tbl_siswa.nama_lengkap', 'ASC')->paginate($per_page)->withQueryString();

        return Inertia::render('Admin/Kesiswaan/BukuInduk/Index', [
            'kelas' => $kelas,
            'filter_kelas' => $filter_kelas,
            'search' => $search,
            'per_page' => $per_page,
            'siswa' => $siswa
        ]);
    }

    // 2. FORM KELENGKAPAN DATA BUKU INDUK
    public function detail($id_siswa)
    {
        $siswa = DB::table('tbl_siswa')
            ->select('tbl_siswa.*', 'tbl_kelas.nama_kelas')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_siswa.id', $id_siswa)
            ->first();

        if (!$siswa) {
            return redirect()->route('admin.kesiswaan.buku_induk.index')->with('error', 'Data siswa tidak ditemukan.');
        }

        $detail = DB::table('tbl_siswa_detail')->where('siswa_id', $id_siswa)->first();
        
        if (!$detail) {
            $detail = (object) [
                'nama_panggilan' => '',
                'jml_saudara_kandung' => 0, 'jml_saudara_tiri' => 0, 'jml_saudara_angkat' => 0,
                'status_yatim_piatu' => '-', 'bahasa_sehari_hari' => 'Bahasa Indonesia',
                'tinggal_bersama' => 'Orang Tua', 'jarak_ke_sekolah' => '', 'transportasi' => '',
                'no_sttb_smp' => '', 'tgl_sttb_smp' => null, 'lama_belajar_smp' => '',
                'pendidikan_ayah' => '', 'pendidikan_ibu' => '', 'penghasilan_ayah' => '', 'penghasilan_ibu' => '',
                'pendidikan_wali' => '', 'penghasilan_wali' => '',
                'gol_darah' => '-', 'tinggi_badan' => 0, 'berat_badan' => 0,
                'penyakit_pernah_diderita' => '', 'kelainan_jasmani' => '-',
                'tgl_diterima' => date('Y-m-d'), 'tgl_meninggalkan' => null, 'alasan_meninggalkan' => '',
                'no_ijazah_smk' => '', 'tinggi_meninggalkan' => 0, 'berat_meninggalkan' => 0,
                'iq' => '', 'tgl_tes_iq' => '', 'prestasi_siswa' => '',
                'kepribadian' => '{}', 'bakat_prestasi' => '[]', 'penerimaan_siswa' => '[]'
            ];
        }

        // DECODE JSON MATRIX UNTUK BAGIAN G & J
        $kepribadian = json_decode($detail->kepribadian ?? '{}', true);
        $bakat = json_decode($detail->bakat_prestasi ?? '[]', true);
        $penerimaan = json_decode($detail->penerimaan_siswa ?? '[]', true);

        return Inertia::render('Admin/Kesiswaan/BukuInduk/Detail', [
            'siswa' => $siswa,
            'detail' => $detail,
            'kepribadian' => $kepribadian ?: new \stdClass(),
            'bakat' => $bakat ?: [],
            'penerimaan' => $penerimaan ?: []
        ]);
    }

    // 3. PROSES SIMPAN KELENGKAPAN
    public function simpanDetail(Request $request)
    {
        $siswa_id = $request->input('siswa_id');
        
        $data = [
            'siswa_id' => $siswa_id,
            'nama_panggilan' => $request->input('nama_panggilan'),
            'jml_saudara_kandung' => $request->input('jml_saudara_kandung') ?: 0,
            'jml_saudara_tiri' => $request->input('jml_saudara_tiri') ?: 0,
            'jml_saudara_angkat' => $request->input('jml_saudara_angkat') ?: 0,
            'status_yatim_piatu' => $request->input('status_yatim_piatu'),
            'bahasa_sehari_hari' => $request->input('bahasa_sehari_hari'),
            'tinggal_bersama' => $request->input('tinggal_bersama'),
            'jarak_ke_sekolah' => $request->input('jarak_ke_sekolah'),
            'transportasi' => $request->input('transportasi'),
            'no_sttb_smp' => $request->input('no_sttb_smp'),
            'tgl_sttb_smp' => empty($request->input('tgl_sttb_smp')) ? null : $request->input('tgl_sttb_smp'),
            'lama_belajar_smp' => $request->input('lama_belajar_smp'),
            'pendidikan_ayah' => $request->input('pendidikan_ayah'),
            'pendidikan_ibu' => $request->input('pendidikan_ibu'),
            'penghasilan_ayah' => $request->input('penghasilan_ayah'),
            'penghasilan_ibu' => $request->input('penghasilan_ibu'),
            'pendidikan_wali' => $request->input('pendidikan_wali'),
            'penghasilan_wali' => $request->input('penghasilan_wali'),
            'gol_darah' => $request->input('gol_darah'),
            'tinggi_badan' => $request->input('tinggi_badan') ?: 0,
            'berat_badan' => $request->input('berat_badan') ?: 0,
            'penyakit_pernah_diderita' => $request->input('penyakit_pernah_diderita'),
            'kelainan_jasmani' => $request->input('kelainan_jasmani'),
            'tgl_diterima' => empty($request->input('tgl_diterima')) ? null : $request->input('tgl_diterima'),
            'tgl_meninggalkan' => empty($request->input('tgl_meninggalkan')) ? null : $request->input('tgl_meninggalkan'),
            'alasan_meninggalkan' => $request->input('alasan_meninggalkan'),
            'no_ijazah_smk' => $request->input('no_ijazah_smk'),
            'tinggi_meninggalkan' => $request->input('tinggi_meninggalkan') ?: 0,
            'berat_meninggalkan' => $request->input('berat_meninggalkan') ?: 0,

            // --- DATA BARU (G, I, J) ---
            'iq' => $request->input('iq'),
            'tgl_tes_iq' => $request->input('tgl_tes_iq'),
            'prestasi_siswa' => $request->input('prestasi_siswa'),
            
            // ENCODE MATRIX JADI JSON
            'kepribadian' => json_encode($request->input('kepribadian', [])),
            'bakat_prestasi' => json_encode($request->input('bakat', [])),
            'penerimaan_siswa' => json_encode($request->input('penerimaan', []))
        ];

        $cek = DB::table('tbl_siswa_detail')->where('siswa_id', $siswa_id)->count();

        if ($cek > 0) {
            DB::table('tbl_siswa_detail')->where('siswa_id', $siswa_id)->update($data);
        } else {
            DB::table('tbl_siswa_detail')->insert($data);
        }

        return redirect()->route('admin.kesiswaan.buku_induk.detail', $siswa_id)->with('success', 'Data kelengkapan Buku Induk beserta Matrix Kepribadian berhasil disimpan utuh!');
    }

    // 4. CETAK PDF BUKU INDUK (PORTRAIT)
    public function cetakPdf($id_siswa)
    {
        ini_set('memory_limit', '1024M'); // Obat kuat PDF

        // a. Ambil Biodata Siswa & Orang Tua
        $siswa = DB::table('tbl_siswa')
            ->select('tbl_siswa.*', 'tbl_kelas.nama_kelas', 'tbl_jurusan.nama_jurusan')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->leftJoin('tbl_jurusan', 'tbl_jurusan.id', '=', 'tbl_siswa.jurusan_id')
            ->where('tbl_siswa.id', $id_siswa)
            ->first();

        if (!$siswa) return redirect()->route('admin.kesiswaan.buku_induk.index')->with('error', 'Data siswa tidak ditemukan.');

        // b. Ambil Detail (Kesehatan, Hobi, Tgl Diterima dll)
        $detail = DB::table('tbl_siswa_detail')->where('siswa_id', $id_siswa)->first();
        if (!$detail) {
            // Create dummy empty object if null to prevent views from crashing
            $detail = json_decode('{}');
        }

        // c. Ambil Data Sekolah (untuk Kop)
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first();

        // d. TARIK DATA REKAP PKL
        $data_pkl = DB::table('tbl_pkl_nilai')
            ->select('tbl_dudi.nama_dudi', 'tbl_pkl_nilai.nilai_akhir', 'tbl_pkl_nilai.predikat')
            ->join('tbl_pkl', 'tbl_pkl.id', '=', 'tbl_pkl_nilai.pkl_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.siswa_id', $id_siswa)
            ->get();

        // e. TARIK DATA REKAP EKSKUL
        $data_ekskul = DB::table('tbl_ekskul_nilai')
            ->select('tbl_ekskul_nilai.nilai_huruf', 'tbl_ekskul.nama_ekskul')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_nilai.ekskul_id')
            ->where('tbl_ekskul_nilai.siswa_id', $id_siswa)
            ->get();

        // f. Tarik Histori Kehadiran
        $kehadiran_history = DB::table('tbl_kehadiran')
            ->select('tbl_kehadiran.*', 'tbl_kelas.nama_kelas')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_kehadiran.id_kelas')
            ->where('tbl_kehadiran.siswa_id', $id_siswa)
            ->orderBy('tbl_kehadiran.id', 'ASC')
            ->limit(6)->get();

        // g. Tarik Histori Nilai Rapor
        $kelas_rapor = DB::table('tbl_nilai_akhir')
            ->select('tbl_nilai_akhir.id_kelas', 'tbl_kelas.nama_kelas')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_nilai_akhir.id_kelas')
            ->where('tbl_nilai_akhir.siswa_id', $id_siswa)
            ->groupBy('tbl_nilai_akhir.id_kelas', 'tbl_kelas.nama_kelas')
            ->orderBy('tbl_nilai_akhir.id_kelas', 'ASC')
            ->limit(6)->get();

        $rapor_history = [];
        foreach ($kelas_rapor as $kr) {
            $nilai_siswa = DB::table('tbl_nilai_akhir')
                ->where('siswa_id', $id_siswa)
                ->where('id_kelas', $kr->id_kelas)
                ->get();
                
            $jml_mapel = $nilai_siswa->count();
            $total_nilai = $nilai_siswa->sum('nilai');
            $rata = $jml_mapel > 0 ? round($total_nilai / $jml_mapel, 2) : 0;

            // Engine Mini: Hitung Ranking Kelas Otomatis
            $query_rank = DB::select("SELECT siswa_id, SUM(nilai) as total_skor FROM tbl_nilai_akhir WHERE id_kelas = ? GROUP BY siswa_id ORDER BY total_skor DESC", [$kr->id_kelas]);
            
            $ranking = '-'; $peringkat = 1;
            foreach ($query_rank as $qr) {
                if ($qr->siswa_id == $id_siswa) { $ranking = $peringkat; break; }
                $peringkat++;
            }

            $rapor_history[] = [
                'nama_kelas' => $kr->nama_kelas,
                'jml_mapel' => $jml_mapel,
                'rata' => $rata,
                'ranking' => $ranking
            ];
        }

        // h. Status Kenaikan Kelas
        $kenaikan_history = DB::table('tbl_kenaikan')->where('siswa_id', $id_siswa)->orderBy('id', 'ASC')->limit(3)->get();

        // Decode JSON Matrix G, I, J
        $kepribadian = json_decode($detail->kepribadian ?? '{}', true) ?: [];
        $bakat = json_decode($detail->bakat_prestasi ?? '[]', true) ?: [];
        $penerimaan = json_decode($detail->penerimaan_siswa ?? '[]', true) ?: [];

        $pdf = Pdf::loadView('cetak.buku_induk', [
            'sekolah' => $sekolah, 
            'siswa' => $siswa, 
            'detail' => $detail,
            'data_pkl' => $data_pkl, 
            'data_ekskul' => $data_ekskul,
            'kehadiran_history' => $kehadiran_history, 
            'rapor_history' => $rapor_history, 
            'kenaikan_history' => $kenaikan_history,
            'kepribadian' => $kepribadian, 
            'bakat' => $bakat, 
            'penerimaan' => $penerimaan
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('Buku_Induk_' . $siswa->nama_lengkap . '.pdf');
    }
}
