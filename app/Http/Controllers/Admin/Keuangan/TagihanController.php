<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JenisBayar;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagihanController extends Controller
{
    public function kelola(Request $request, $id_jenis)
    {
        $info = JenisBayar::with(['posBayar', 'tahunAjaran'])->findOrFail($id_jenis);
        
        $filterKelas = $request->get('kelas');

        $query = Tagihan::with(['siswa', 'kelas'])
            ->where('id_jenis_bayar', $id_jenis);

        if ($filterKelas) {
            $query->where('id_kelas', $filterKelas);
        }

        $tagihan = $query->orderBy(Kelas::select('nama_kelas')->whereColumn('tbl_kelas.id', 'tbl_tagihan.id_kelas'), 'asc')
            ->orderBy(Siswa::select('nama_lengkap')->whereColumn('tbl_siswa.id', 'tbl_tagihan.id_siswa'), 'asc')
            ->orderBy('bulan_ke', 'asc')
            ->get();

        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Tagihan/Kelola', [
            'info'    => $info,
            'tagihan' => $tagihan,
            'kelas'   => $kelas,
            'filter'  => $filterKelas
        ]);
    }

    public function generate(Request $request)
    {
        $id_jenis = $request->id_jenis_bayar;
        $kelas_ids = $request->id_kelas; 

        if (empty($kelas_ids)) {
            return back()->with('error', 'Harap pilih minimal satu kelas!');
        }

        $jenis = JenisBayar::with(['posBayar', 'jenisBayarJurusan'])->findOrFail($id_jenis);
        
        // Kita juga perlu tahu jurusan siswa, panggil relasi kelas untuk dapet id_jurusan
        $siswa = Siswa::with('kelas')->whereIn('kelas_id', $kelas_ids)->get();

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa di kelas yang dipilih.');
        }

        // Siapkan map tarif jurusan jika is_per_jurusan
        $tarifJurusan = [];
        $tarifJurusanPutri = [];
        if ($jenis->is_per_jurusan) {
            foreach ($jenis->jenisBayarJurusan as $jbj) {
                $tarifJurusan[$jbj->id_jurusan] = $jbj->nominal;
                $tarifJurusanPutri[$jbj->id_jurusan] = $jbj->nominal_putri;
            }
        }

        $sukses = 0;
        $bulanIndo = [1=>'Juli', 2=>'Agustus', 3=>'September', 4=>'Oktober', 5=>'November', 6=>'Desember', 7=>'Januari', 8=>'Februari', 9=>'Maret', 10=>'April', 11=>'Mei', 12=>'Juni'];

        foreach ($siswa as $s) {
            // Tentukan tarif untuk siswa ini
            $isPerempuan = in_array(strtolower($s->jk ?? $s->jenis_kelamin ?? 'l'), ['p', 'perempuan', 'wanita']);
            
            $nominal_tagihan = $jenis->nominal_default;
            if ($jenis->is_beda_gender && $isPerempuan) {
                $nominal_tagihan = $jenis->nominal_putri_default;
            }

            if ($jenis->is_per_jurusan) {
                // Ambil dari jurusan yang nyantol di siswa atau nyantol di kelas
                $jurId = $s->jurusan_id ?? $s->kelas->id_jurusan ?? null;
                $nominal_tagihan = $tarifJurusan[$jurId] ?? 0;
                
                if ($jenis->is_beda_gender && $isPerempuan) {
                    $nominal_tagihan = $tarifJurusanPutri[$jurId] ?? 0;
                }
            }

            if ($jenis->tipe_bayar == 'BEBAS') {
                $exist = Tagihan::where('id_jenis_bayar', $id_jenis)
                                ->where('id_siswa', $s->id)
                                ->count();
                
                if ($exist == 0) {
                    Tagihan::create([
                        'id_jenis_bayar' => $id_jenis,
                        'id_siswa'       => $s->id, 
                        'id_kelas'       => $s->kelas_id,
                        'nominal_tagihan'=> $nominal_tagihan,
                        'keterangan'     => $jenis->posBayar->nama_pos ?? 'Tagihan Bebas'
                    ]);
                    $sukses++;
                }
            } else {
                foreach ($bulanIndo as $idx => $bln) {
                    $exist = Tagihan::where('id_jenis_bayar', $id_jenis)
                                    ->where('id_siswa', $s->id)
                                    ->where('bulan_ke', $idx)
                                    ->count();

                    if ($exist == 0) {
                        Tagihan::create([
                            'id_jenis_bayar' => $id_jenis,
                            'id_siswa'       => $s->id, 
                            'id_kelas'       => $s->kelas_id,
                            'nominal_tagihan'=> $nominal_tagihan,
                            'keterangan'     => $bln, 
                            'bulan_ke'       => $idx
                        ]);
                        $sukses++;
                    }
                }
            }
        }

        return redirect()->route('admin.keuangan.tagihan.kelola', $id_jenis)->with('message', "$sukses tagihan berhasil dibuat.");
    }

    public function updateNominal(Request $request)
    {
        $id = $request->pk;
        $nominal = str_replace('.', '', $request->value); 

        $tagihan = Tagihan::findOrFail($id);
        
        if ($tagihan->nominal_terbayar > 0 && $nominal < $tagihan->nominal_terbayar) {
            return response()->json(['status' => 'error', 'message' => 'Gagal! Nominal tagihan tidak boleh lebih kecil dari yang sudah dibayar.'], 400);
        }

        $tagihan->update(['nominal_tagihan' => $nominal]);
        return response()->json(['status' => 'success', 'message' => 'Nominal tagihan berhasil diperbarui.']);
    }
}
