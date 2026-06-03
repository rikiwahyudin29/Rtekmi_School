<?php
$pathKop = 'uploads/identitas/' . ($sekolah->kop_surat ?? 'default.png');
$pakaiKopGambar = !empty($sekolah->kop_surat) && file_exists(public_path($pathKop));
$pathLogo = !empty($sekolah->logo) && file_exists(public_path('uploads/identitas/' . $sekolah->logo)) ? asset('uploads/identitas/' . $sekolah->logo) : asset('assets/img/logo.png'); 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan Keuangan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 20px; text-align: center; }
        .kop-surat img.img-kop { width: 100%; height: auto; } 
        .kop-surat-inner { border-bottom: 3px solid #000; padding-bottom: 10px; display: flex; width: 100%; align-items: center; margin-bottom: 2px;}
        .kop-logo { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; }
        .kop-text { text-align: center; flex: 1; }
        .kop-text h1, .kop-text h2, .kop-text h3 { margin: 0; font-weight: bold; }
        .kop-text h2 { font-size: 16px; }
        .kop-text h1 { font-size: 20px; margin: 5px 0; }
        .kop-text p { margin: 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .title-section { font-size: 14px; font-weight: bold; margin: 15px 0 5px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="kop-surat">
        @if($pakaiKopGambar)
            <img src="{{ asset($pathKop) }}" alt="Kop Surat" class="img-kop">
        @else
            <div class="kop-surat-inner">
                <img src="{{ $pathLogo }}" alt="Logo Sekolah" class="kop-logo">
                <div class="kop-text">
                    <h2>PEMERINTAH PROVINSI {{ strtoupper($sekolah->provinsi ?? 'JAWA BARAT') }}</h2>
                    <h1>{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</h1>
                    <p>{{ $sekolah->alamat ?? 'Alamat Sekolah' }} | Telp: {{ $sekolah->no_telp ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="text-center" style="margin-bottom: 20px;">
        <h3 style="margin:0;">LAPORAN KEUANGAN</h3>
        <p style="margin:5px 0 0 0;">Periode: {{ date('d/m/Y', strtotime($start)) }} s.d {{ date('d/m/Y', strtotime($end)) }}</p>
    </div>

    @php
        $totalMasuk = 0;
        $totalKeluar = 0;
    @endphp

    <div class="title-section">A. PEMASUKAN (PEMBAYARAN SISWA)</div>
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Siswa</th>
                <th width="35%">Keterangan</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $i => $t)
                @php 
                    $totalMasuk += $t->jumlah_bayar; 
                    $ket_bayar = $t->tagihan->jenisBayar->posBayar->nama_pos ?? 'Pembayaran';
                    $ket_tambahan = $t->tagihan->keterangan ? ' - ' . $t->tagihan->keterangan : '';
                    if (!$t->tagihan->keterangan && $t->tagihan->bulan_ke > 0) {
                        $ket_tambahan = ' (Bulan ' . $t->tagihan->bulan_ke . ')';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($t->created_at)) }}</td>
                    <td>{{ $t->siswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $ket_bayar . $ket_tambahan }}</td>
                    <td class="text-right">{{ number_format($t->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada pemasukan</td></tr>
            @endforelse
            <tr>
                <td colspan="4" class="text-right bold">TOTAL PEMASUKAN</td>
                <td class="text-right bold">{{ number_format($totalMasuk, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="title-section">B. PENGELUARAN OPERASIONAL</div>
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Judul & Divisi</th>
                <th width="35%">Keterangan</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengeluaran as $i => $p)
                @php $totalKeluar += $p->nominal; @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ date('d/m/Y', strtotime($p->tanggal)) }}</td>
                    <td>{{ $p->judul_pengeluaran }} ({{ $p->divisi->nama_divisi ?? '-' }})</td>
                    <td>{{ $p->keterangan }}</td>
                    <td class="text-right">{{ number_format($p->nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada pengeluaran</td></tr>
            @endforelse
            <tr>
                <td colspan="4" class="text-right bold">TOTAL PENGELUARAN</td>
                <td class="text-right bold">{{ number_format($totalKeluar, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <br>
    <table style="width: 50%; margin-left: auto;">
        <tr>
            <td class="bold">TOTAL PEMASUKAN</td>
            <td class="text-right">{{ number_format($totalMasuk, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="bold">TOTAL PENGELUARAN</td>
            <td class="text-right">{{ number_format($totalKeluar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="bold" style="font-size: 14px;">SURPLUS / DEFISIT</td>
            <td class="text-right bold" style="font-size: 14px;">{{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="margin-top: 50px; text-align: right; margin-right: 50px;">
        <p>Cetak pada: {{ date('d/m/Y H:i') }}</p>
        <p>Mengetahui,</p>
        <br><br><br>
        <p><b>_________________________</b></p>
        <p>Bagian Keuangan</p>
    </div>
</body>
</html>
