<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Transaksi;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\LogKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripayCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json, true);

        // Get config from DB
        $privateKey = env('TRIPAY_PRIVATE_KEY');
        if (empty($privateKey)) {
            Log::error('TripayCallback: Private key is empty');
            return response('Configuration Error', 500);
        }

        // Signature Validation
        $callbackSignature = $request->header('X-Callback-Signature');
        $localSignature = hash_hmac('sha256', $json, $privateKey);

        if ($callbackSignature !== $localSignature) {
            Log::error('TripayCallback: Invalid Signature');
            return response('Invalid Signature', 403);
        }

        // Process only if PAID
        if (isset($data['status']) && $data['status'] === 'PAID') {
            $merchant_ref = $data['merchant_ref'];

            $transaksi = Transaksi::where('merchant_ref', $merchant_ref)
                ->where('status_transaksi', '!=', 'SUCCESS')
                ->first();

            if ($transaksi) {
                // Update Transaksi
                $transaksi->update([
                    'status_transaksi' => 'SUCCESS',
                ]);

                // Update Tagihan
                $tagihan = Tagihan::find($transaksi->id_tagihan);
                
                if ($tagihan) {
                    $bayar_baru = $tagihan->nominal_terbayar + $transaksi->jumlah_bayar;
                    $status_baru = ($bayar_baru >= $tagihan->nominal_tagihan) ? 'LUNAS' : 'CICIL';

                    $tagihan->update([
                        'nominal_terbayar' => $bayar_baru,
                        'status_bayar'     => $status_baru
                    ]);

                    // Send WA logic can be placed here
                    Log::info("SYSTEM: Pembayaran Diterima via Tripay Ref: $merchant_ref (LUNAS/CICIL)");

                    // Catat ke Log Keuangan
                    $siswa = Siswa::find($transaksi->id_siswa);
                    $nama_siswa = $siswa ? $siswa->nama_lengkap : 'Siswa';
                    LogKeuangan::create([
                        'user_id'     => 0, // 0 for system
                        'nama_user'   => 'Sistem (Tripay)',
                        'role'        => 'sistem',
                        'ip_address'  => $request->ip(),
                        'device_info' => 'Tripay Callback',
                        'aksi'        => "Pembayaran online otomatis diterima sebesar Rp " . number_format($transaksi->jumlah_bayar, 0, ',', '.') . " untuk tagihan #" . $tagihan->id . " atas nama $nama_siswa"
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
