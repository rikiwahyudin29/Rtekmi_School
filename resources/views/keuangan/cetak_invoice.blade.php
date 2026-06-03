<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran - {{ $trx->kode_transaksi }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f8;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .kop-surat {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
            max-height: 120px;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 40px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            width: 48%;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 120px;
            font-weight: bold;
            color: #555;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 30%;
            text-align: center;
        }
        .signature-space {
            height: 80px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        @media print {
            body { background-color: transparent; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Kop Surat -->
        <div class="kop-surat">
            @if($sekolah && $sekolah->kop_surat)
                <img src="{{ asset('uploads/sekolah/' . $sekolah->kop_surat) }}" alt="Kop Surat">
            @else
                <!-- Fallback if no kop surat uploaded -->
                @if($sekolah && $sekolah->logo)
                    <img src="{{ asset('uploads/sekolah/' . $sekolah->logo) }}" alt="Logo" style="height: 80px; margin-bottom:10px;">
                @endif
                <h2 style="margin: 0;">{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h2>
                <p style="margin: 5px 0 0 0;">{{ $sekolah->alamat ?? 'Alamat Sekolah' }}</p>
                <p style="margin: 5px 0 0 0;">Telp: {{ $sekolah->telepon ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
            @endif
        </div>

        <div class="title">INVOICE PEMBAYARAN</div>
        <div class="subtitle">Bukti Pembayaran Administrasi Sekolah</div>

        <div class="info-section">
            <div class="info-box">
                <table class="info-table">
                    <tr>
                        <td>No. Transaksi</td>
                        <td>: {{ $trx->kode_transaksi }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($trx->tanggal_bayar)->translatedFormat('d F Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: <span style="color: green; font-weight: bold;">{{ $trx->status_transaksi }}</span></td>
                    </tr>
                </table>
            </div>
            <div class="info-box">
                <table class="info-table">
                    <tr>
                        <td>Nama Siswa</td>
                        <td>: {{ $trx->siswa->nama_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>NISN / NIS</td>
                        <td>: {{ $trx->siswa->nisn ?? '-' }} / {{ $trx->siswa->nis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Kelas</td>
                        <td>: {{ $trx->siswa->kelas->nama_kelas ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="details-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="55%">Deskripsi Pembayaran</th>
                    <th width="40%" class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong>{{ $trx->tagihan->jenisBayar->posBayar->nama_pos ?? 'Tagihan' }}</strong><br>
                        <span style="color: #666; font-size: 12px;">{{ $trx->tagihan->keterangan }}</span>
                    </td>
                    <td class="text-right">Rp {{ number_format($trx->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2" class="text-right">TOTAL PEMBAYARAN</td>
                    <td class="text-right">Rp {{ number_format($trx->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <p>Penyetor/Siswa</p>
                <div class="signature-space"></div>
                <p><strong>{{ $trx->siswa->nama_lengkap ?? '(.........................)' }}</strong></p>
            </div>
            <div class="signature-box" style="visibility: hidden;">
                <!-- Spacer -->
            </div>
            <div class="signature-box">
                <p>{{ $sekolah->kabupaten ?? 'Kota' }}, {{ \Carbon\Carbon::parse($trx->tanggal_bayar)->translatedFormat('d F Y') }}</p>
                <p>Petugas Kasir</p>
                <div class="signature-space"></div>
                <p><strong>{{ $trx->petugas->nama_lengkap ?? 'Sistem' }}</strong></p>
            </div>
        </div>

        <div class="footer">
            Dokumen ini dicetak otomatis oleh sistem dan sah sebagai bukti pembayaran yang dikeluarkan oleh {{ $sekolah->nama_sekolah ?? 'Sekolah' }}.<br>
            Harap simpan dengan baik.
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
