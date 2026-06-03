<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $trx->kode_transaksi }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .receipt-container {
            width: 58mm; /* Standard thermal size */
            margin: 0 auto;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .logo {
            width: 40px;
            height: auto;
            margin-bottom: 5px;
        }
        .flex { display: flex; justify-content: space-between; }
        @media print {
            body { background-color: transparent; }
            .receipt-container { box-shadow: none; padding: 0; margin: 0; width: 100%; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center">
            @if($sekolah && $sekolah->logo)
                <img src="{{ asset('uploads/identitas/' . $sekolah->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="font-bold">{{ $sekolah->nama_sekolah ?? 'SIAKAD' }}</div>
            <div style="font-size: 10px;">{{ $sekolah->alamat ?? '' }}</div>
        </div>
        
        <hr>
        
        <!-- Info Transaksi -->
        <div>
            <div class="flex"><span>No:</span> <span>{{ $trx->kode_transaksi }}</span></div>
            <div class="flex"><span>Tgl:</span> <span>{{ \Carbon\Carbon::parse($trx->tanggal_bayar)->format('d/m/Y H:i') }}</span></div>
            <div class="flex"><span>Kasir:</span> <span>{{ $trx->petugas->nama_lengkap ?? 'Sistem' }}</span></div>
        </div>
        
        <hr>
        
        <!-- Info Siswa -->
        <div>
            <div class="font-bold mb-1">{{ $trx->siswa->nama_lengkap ?? '-' }}</div>
            <div class="flex"><span>NISN:</span> <span>{{ $trx->siswa->nisn ?? '-' }}</span></div>
            <div class="flex"><span>Kelas:</span> <span>{{ $trx->siswa->kelas->nama_kelas ?? '-' }}</span></div>
        </div>
        
        <hr>
        
        <!-- Rincian -->
        <div>
            <div class="font-bold mb-1">Tagihan Dibayar:</div>
            @php
                $pos = $trx->tagihan->jenisBayar->posBayar->nama_pos ?? 'Tagihan';
                $ket = $trx->tagihan->keterangan;
                $desc = (strtolower($pos) == strtolower($ket)) ? $pos : $pos . ' - ' . $ket;
                $ta = $trx->tagihan->jenisBayar->tahunAjaran->tahun_ajaran ?? '-';
                
                $statusTagihan = $trx->tagihan->status_bayar;
                $sisa = $trx->tagihan->nominal_tagihan - $trx->tagihan->nominal_terbayar;
            @endphp
            <div>{{ $desc }}</div>
            <div style="font-size: 10px; color: #555;">Tahun Ajaran: {{ $ta }}</div>
            
            <div class="flex mt-2 font-bold" style="font-size: 14px;">
                <span>BAYAR:</span> 
                <span>Rp {{ number_format($trx->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            
            <div class="mt-2" style="font-size: 11px;">
                <div class="flex"><span>Status Tagihan:</span> <span>{{ $statusTagihan }}</span></div>
                @if($statusTagihan != 'LUNAS')
                <div class="flex"><span>Sisa Tagihan:</span> <span>Rp {{ number_format($sisa, 0, ',', '.') }}</span></div>
                @endif
            </div>
        </div>
        
        <hr>
        
        <!-- Footer -->
        <div class="text-center mt-2" style="font-size: 10px;">
            <div>Status: <span class="font-bold">{{ $trx->status_transaksi }}</span></div>
            <div class="mt-2">Terima kasih atas pembayaran Anda.</div>
            <div>Harap simpan struk ini sebagai bukti pembayaran yang sah.</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
