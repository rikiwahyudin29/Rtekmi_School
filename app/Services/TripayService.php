<?php

namespace App\Services;

use App\Models\Sekolah;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    private $apiKey;
    private $privateKey;
    private $merchantCode;
    private $mode;

    public function __construct()
    {
        $this->apiKey       = env('TRIPAY_API_KEY');
        $this->privateKey   = env('TRIPAY_PRIVATE_KEY');
        $this->merchantCode = env('TRIPAY_MERCHANT_CODE');
        $this->mode         = strtolower(env('TRIPAY_MODE', 'sandbox')); 
    }

    public function getBaseUrl()
    {
        return ($this->mode === 'production') 
            ? 'https://tripay.co.id/api/' 
            : 'https://tripay.co.id/api-sandbox/';
    }

    // 1. AMBIL DAFTAR CHANNEL PEMBAYARAN (QRIS, ALFAMART, DLL)
    public function getChannels()
    {
        if (empty($this->apiKey)) {
            return [];
        }

        $url = $this->getBaseUrl() . 'merchant/payment-channel';

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                $result = $response->json();
                return (isset($result['success']) && $result['success']) ? $result['data'] : [];
            } else {
                Log::error('Tripay Error getChannels: ' . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Tripay Exception getChannels: ' . $e->getMessage());
            return [];
        }
    }

    // 2. REQUEST TRANSAKSI BARU
    public function requestTransaction($data)
    {
        if (empty($this->apiKey) || empty($this->privateKey) || empty($this->merchantCode)) {
            return ['success' => false, 'message' => 'Konfigurasi Tripay di Pengaturan Sekolah Belum Lengkap!'];
        }

        $url = $this->getBaseUrl() . 'transaction/create';
        
        $signature = hash_hmac('sha256', $this->merchantCode . $data['merchant_ref'] . $data['amount'], $this->privateKey);

        $payload = [
            'method'         => $data['method'],
            'merchant_ref'   => $data['merchant_ref'],
            'amount'         => $data['amount'],
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'order_items'    => $data['order_items'],
            'return_url'     => route('siswa.keuangan.index'), // Kita perlu pastikan route ini ada nanti
            'expired_time'   => (time() + (24 * 60 * 60)), // Expire 24 jam
            'signature'      => $signature
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->asForm() // Mengirim data sebagai Form URL Encoded
                ->timeout(30)
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Tripay Error requestTransaction: ' . $response->body());
                return ['success' => false, 'message' => 'Gagal koneksi ke Tripay API. HTTP Code: ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('Tripay Exception requestTransaction: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
