<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Carbon\Carbon;

class KelasVirtualController extends Controller
{
    public function index()
    {
        $guru = DB::table('tbl_guru')->where('user_id', auth()->id())->first();

        $jadwal = DB::table('tbl_kelas_virtual as v')
            ->select('v.*', 'k.nama_kelas')
            ->join('tbl_kelas as k', 'k.id', '=', 'v.kelas_id')
            ->where('v.guru_id', $guru->id)
            ->orderBy('v.id', 'DESC')
            ->get();

        $jadwalIds = DB::table('tbl_jadwal')->where('id_guru', $guru->id)->get();
        $kelasIds = $jadwalIds->pluck('id_kelas')->unique();
        
        $kelas = DB::table('tbl_kelas')->whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();

        // Check connection status
        $isConnected = false;
        if ($guru && !empty($guru->google_refresh_token)) {
            $isConnected = true;
        }

        return Inertia::render('Guru/ELearning/KelasVirtual/Index', [
            'jadwal' => $jadwal,
            'kelas' => $kelas,
            'isConnected' => $isConnected,
            'googleAuthUrl' => $this->getGoogleAuthUrl()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapel' => 'required',
            'kelas_id' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $guru = DB::table('tbl_guru')->where('user_id', auth()->id())->first();

        if (!$guru || empty($guru->google_refresh_token)) {
            return back()->with('error', 'Silakan hubungkan akun Google terlebih dahulu!');
        }

        $tgl_hari_ini = date('Y-m-d');
        $full_start = Carbon::parse($tgl_hari_ini . ' ' . $request->jam_mulai)->format('Y-m-d\TH:i:sP');
        $full_end = Carbon::parse($tgl_hari_ini . ' ' . $request->jam_selesai)->format('Y-m-d\TH:i:sP');

        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        
        $oldToken = json_decode($guru->google_refresh_token, true);
        $client->setAccessToken($oldToken);

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if (isset($newToken['error'])) {
                    return back()->with('error', 'Sesi Google tidak valid. Silakan hubungkan ulang akun Google.');
                }
                if (!isset($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $client->getRefreshToken();
                }
                DB::table('tbl_guru')->where('id', $guru->id)->update([
                    'google_refresh_token' => json_encode($newToken)
                ]);
            } else {
                return back()->with('error', 'Akses Google kedaluwarsa. Silakan putuskan dan hubungkan ulang akun Google.');
            }
        }

        $service = new Calendar($client);

        $event = new Event([
            'summary'     => 'Kelas: ' . $request->mapel,
            'description' => 'Dibuat otomatis via Sistem Sekolah',
            'start'       => ['dateTime' => $full_start, 'timeZone' => 'Asia/Jakarta'],
            'end'         => ['dateTime' => $full_end, 'timeZone' => 'Asia/Jakarta'],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(), 
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ]
            ]
        ]);

        try {
            $event = $service->events->insert('primary', $event, ['conferenceDataVersion' => 1]);
            $meetLink = $event->getHangoutLink();

            if (!$meetLink) {
                return back()->with('error', 'Google Calendar berhasil dibuat, tapi Link Meet gagal di-generate.');
            }

            DB::table('tbl_kelas_virtual')->insert([
                'guru_id'         => $guru->id,
                'kelas_id'        => $request->kelas_id,
                'mapel_id'        => 0, 
                'judul_pertemuan' => $request->mapel,        
                'tgl_pertemuan'   => Carbon::parse($tgl_hari_ini . ' ' . $request->jam_mulai),   
                'link_meet'       => $meetLink,     
                'status'          => 'Aktif'        
            ]);

            return back()->with('success', 'Link Google Meet berhasil dibuat!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat meeting: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $guru = DB::table('tbl_guru')->where('user_id', auth()->id())->first();
        DB::table('tbl_kelas_virtual')->where('id', $id)->where('guru_id', $guru->id)->delete();
        
        return back()->with('success', 'Sesi Kelas Virtual berhasil dihapus.');
    }

    private function getGoogleAuthUrl()
    {
        // Setup client auth URL logic if needed, or leave empty if handled elsewhere
        // Usually you have a separate route for Google OAuth like /auth/google/redirect
        return route('dashboard'); // Placeholder
    }
}
