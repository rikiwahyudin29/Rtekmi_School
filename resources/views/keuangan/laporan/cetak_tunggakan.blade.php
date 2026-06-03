<!DOCTYPE html>
<html>
<head>
    <title>Cetak Rekap Tunggakan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h2>
        <p>{{ $sekolah->alamat ?? 'Alamat Sekolah' }}</p>
        <p>Telp: {{ $sekolah->no_telp ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
    </div>

    <div class="text-center" style="margin-bottom: 20px;">
        <h3 style="margin:0;">REKAPITULASI TUNGGAKAN PEMBAYARAN SISWA</h3>
        <p style="margin:5px 0 0 0;">Per Tanggal: {{ date('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Jenis Pembayaran</th>
                <th width="10%" class="text-center">Siswa Lunas</th>
                <th width="10%" class="text-center">Nunggak</th>
                <th width="13%" class="text-right">Potensi (Rp)</th>
                <th width="13%" class="text-right">Masuk (Rp)</th>
                <th width="14%" class="text-right">Tunggakan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gt_potensi = 0;
                $gt_masuk = 0;
                $gt_tunggakan = 0;
            @endphp
            @forelse($rekap as $i => $r)
                @php
                    $gt_potensi += $r->total_tagihan;
                    $gt_masuk += $r->total_bayar;
                    $gt_tunggakan += $r->total_tunggakan;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r->posBayar->nama_pos ?? 'N/A' }} <br> <small>{{ $r->tahunAjaran->tahun_ajaran ?? 'Semua Tahun' }}</small></td>
                    <td class="text-center">{{ $r->qty_lunas }}</td>
                    <td class="text-center">{{ $r->qty_belum }}</td>
                    <td class="text-right">{{ number_format($r->total_tagihan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-right bold" style="color:red;">{{ number_format($r->total_tunggakan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
            @endforelse
            <tr>
                <td colspan="4" class="text-right bold">GRAND TOTAL</td>
                <td class="text-right bold">{{ number_format($gt_potensi, 0, ',', '.') }}</td>
                <td class="text-right bold">{{ number_format($gt_masuk, 0, ',', '.') }}</td>
                <td class="text-right bold">{{ number_format($gt_tunggakan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; margin-right: 50px;">
        <p>Mengetahui,</p>
        <br><br><br>
        <p><b>_________________________</b></p>
        <p>Bagian Keuangan</p>
    </div>
</body>
</html>
