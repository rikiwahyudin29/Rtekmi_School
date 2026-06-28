<?php

namespace App\Services;

use App\Models\Sekolah;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaService
{
    private $token;
    private $url;

    public function __construct()
    {
        $this->token = env('WA_API_TOKEN', '');
        $this->url   = env('WA_API_URL', 'https://api.fonnte.com/send');
    }

    public function kirim($nomor, $pesan)
    {
        // Cek jika token atau nomor kosong
        if (empty($this->token) || empty($nomor)) {
            Log::error('Gagal kirim WA: Token atau Nomor tujuan kosong.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->url, [
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62',
            ]);

            return $response->body();
        } catch (\Exception $e) {
            Log::error('WA API Error: ' . $e->getMessage());
            return false;
        }
    }
}
