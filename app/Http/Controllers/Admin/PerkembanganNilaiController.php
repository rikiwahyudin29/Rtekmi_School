<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Siswa;
use App\Models\RaporAkhir;

class PerkembanganNilaiController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('kelas')->get();
        $selected_siswa_id = request('siswa_id');
        
        $chart_data = null;
        $tabel_data = null;
        $mata_pelajaran = [];

        if ($selected_siswa_id) {
            $rapor = RaporAkhir::with('mapel')
                ->where('siswa_id', $selected_siswa_id)
                ->orderBy('semester', 'asc')
                ->get();
                
            // Format untuk chart (rata-rata per semester)
            $rata_rata_per_semester = [];
            
            // Format untuk tabel
            foreach ($rapor as $r) {
                $nama_mapel = $r->mapel->nama_mapel;
                if (!in_array($nama_mapel, $mata_pelajaran)) {
                    $mata_pelajaran[] = $nama_mapel;
                }
                
                $tabel_data[$nama_mapel][$r->semester] = $r->nilai_akhir;
                
                if (!isset($rata_rata_per_semester[$r->semester])) {
                    $rata_rata_per_semester[$r->semester] = ['total' => 0, 'count' => 0];
                }
                $rata_rata_per_semester[$r->semester]['total'] += $r->nilai_akhir;
                $rata_rata_per_semester[$r->semester]['count']++;
            }
            
            $chart_labels = [];
            $chart_values = [];
            
            for ($i = 1; $i <= 6; $i++) {
                $chart_labels[] = "Semester $i";
                if (isset($rata_rata_per_semester[$i])) {
                    $chart_values[] = round($rata_rata_per_semester[$i]['total'] / $rata_rata_per_semester[$i]['count'], 2);
                } else {
                    $chart_values[] = null;
                }
            }
            
            $chart_data = [
                'labels' => $chart_labels,
                'values' => $chart_values
            ];
        }

        return Inertia::render('Admin/PerkembanganNilai/Index', [
            'siswa' => $siswa,
            'selected_siswa_id' => $selected_siswa_id,
            'chart_data' => $chart_data,
            'tabel_data' => $tabel_data,
            'mata_pelajaran' => $mata_pelajaran
        ]);
    }
}
