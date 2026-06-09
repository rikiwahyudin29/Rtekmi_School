<?php

namespace App\Services;

use App\Models\DapodikSetting;
use Illuminate\Support\Facades\Http;
use Exception;

class DapodikService
{
    protected $baseUrl;
    protected $token;
    protected $npsn;

    public function __construct()
    {
        $setting = DapodikSetting::first();
        if ($setting) {
            // Hilangkan trailing slash jika ada
            $this->baseUrl = rtrim($setting->ip_dapodik, '/');
            $this->token = $setting->key_integrasi;
            $this->npsn = $setting->npsn;
        }
    }

    /**
     * Memeriksa konfigurasi Dapodik.
     */
    private function checkConfig()
    {
        if (!$this->baseUrl || !$this->token || !$this->npsn) {
            throw new Exception("Konfigurasi Dapodik belum lengkap. Silakan cek menu Pengaturan Dapodik.");
        }
    }

    /**
     * Melakukan HTTP GET request ke Dapodik Web Service.
     *
     * @param string $endpoint Contoh: 'getSekolah', 'getGtk'
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get($endpoint, $limit = 500, $offset = 0)
    {
        $this->checkConfig();

        $url = $this->baseUrl . '/WebService/' . $endpoint . '?npsn=' . $this->npsn;

        try {
            $response = Http::withToken($this->token)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // Dapodik biasanya mengembalikan response dalam array 'rows'
                if (isset($data['rows'])) {
                    return $data['rows'];
                }
                return $data;
            }

            throw new Exception("Gagal mengambil data dari Dapodik. Status Code: " . $response->status());
        } catch (Exception $e) {
            throw new Exception("Koneksi ke Web Service Dapodik gagal: " . $e->getMessage());
        }
    }

    /**
     * Melakukan HTTP POST request ke Dapodik Web Service (Tarik/Sinkron/Insert Data).
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    public function post($endpoint, $payload = [])
    {
        $this->checkConfig();

        $url = $this->baseUrl . '/WebService/' . $endpoint;

        try {
            $response = Http::withToken($this->token)->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Gagal mengirim data ke Dapodik. Status Code: " . $response->status());
        } catch (Exception $e) {
            throw new Exception("Koneksi ke Web Service Dapodik gagal: " . $e->getMessage());
        }
    }

    /**
     * Menguji koneksi ke Web Service Dapodik.
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->checkConfig();
            $url = $this->baseUrl . '/WebService/getSekolah?npsn=' . $this->npsn;
            $response = Http::withToken($this->token)->timeout(5)->get($url);
            
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
