<?php

namespace App\Http\Controllers\Admin\CBT;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\UjianSiswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OverviewController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/CBT/Overview/Index', [
            'page_title' => 'Overview Ujian'
        ]);
    }

    public function data(Request $request)
    {
        try {
            // Get passcode from cache or generate a default one
            $passcode = Cache::get('passcode_locked_app', '123456');

            $ujian_today = 0;
            $ujian_active = 0;

            // Start and End Unix timestamp from query
            $startDate = $request->get('startDate', time());
            $endDate = $request->get('endDate', time());

            // Get all schedules
            $jadwalData = JadwalUjian::all();

            foreach ($jadwalData as $value) {
                $startUnix = strtotime($value->waktu_mulai ?? date('Y-m-d H:i:s'));
                $endUnix   = strtotime($value->waktu_selesai ?? date('Y-m-d H:i:s'));

                if (($startUnix >= $startDate && $startUnix <= $endDate) || ($endUnix >= $startDate && $endUnix <= $endDate)) {
                    $ujian_today++;
                }
                
                if ($value->status_ujian === 'AKTIF') {
                    $ujian_active++;
                }
            }

            // Count rows
            $jawaban = DB::table('jawaban_pg_ujian_siswa')->count();
            $ujiansiswa = DB::table('ujian_siswa')->count();

            return response()->json([
                'passcode_locked_app' => $passcode,
                'ujian_today' => $ujian_today,
                'ujian_active' => $ujian_active,
                'jawaban' => $jawaban,
                'ujiansiswa' => $ujiansiswa
            ]);
            
        } catch (\Throwable $e) {
            return response()->json(['message' => "Error Overview: " . $e->getMessage()], 500);
        }
    }

    public function changePasscodeApp()
    {
        $newPass = random_int(100000, 999999);
        Cache::put('passcode_locked_app', $newPass);
        
        return response()->json(['passcode_locked_app' => $newPass]);
    }
}
