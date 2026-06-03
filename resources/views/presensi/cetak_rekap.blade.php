<?php
$pathKop = 'uploads/identitas/' . ($sekolah->kop_surat ?? 'default.png');
$pakaiKopGambar = !empty($sekolah->kop_surat) && file_exists(public_path($pathKop));
$pathLogo = !empty($sekolah->logo) && file_exists(public_path('uploads/identitas/' . $sekolah->logo)) ? asset('uploads/identitas/' . $sekolah->logo) : asset('assets/img/logo.png'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kehadiran Bulanan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 20px; text-align: center; }
        .kop-surat img.img-kop { width: 100%; height: auto; } 
        .kop-surat-inner { border-bottom: 3px solid #000; padding-bottom: 10px; display: flex; width: 100%; align-items: center; border-bottom: 3px solid #000; margin-bottom: 2px;}
        .kop-logo { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; }
        .kop-text { text-align: center; flex: 1; }
        .kop-text h1, .kop-text h2, .kop-text h3 { margin: 0; font-weight: bold; }
        .kop-text h2 { font-size: 16px; }
        .kop-text h1 { font-size: 20px; margin: 5px 0; }
        .kop-text p { margin: 0; font-size: 11px; }
        .judul { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .info { margin-bottom: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; font-size: 10px;}
        td { font-size: 10px; }
        .text-center { text-align: center; }
        .ttd { float: right; width: 300px; text-align: center; margin-top: 30px; }
        @media print {
            @page { size: landscape; margin: 1cm; }
            body { padding: 0; }
        }
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
                    <h1>{{ strtoupper($sekolah->nama_sekolah ?? 'SMK NEGERI 1 CONTOH') }}</h1>
                    <p>{{ $sekolah->alamat ?? 'Jl. Contoh Alamat No. 123' }} | Telp: {{ $sekolah->no_telp ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="judul">
        REKAPITULASI KEHADIRAN {{ strtoupper($tipe ?? 'SISWA') }} BULANAN
    </div>

    <div class="info">
        Bulan : {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }} <br>
        @if(isset($tipe) && $tipe == 'guru')
            Divisi : GURU & STAFF
        @else
            Kelas : {{ $kelas->nama_kelas ?? '' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="3%">No</th>
                <th rowspan="2" width="20%">Nama Lengkap</th>
                <th colspan="7">Total Kehadiran</th>
            </tr>
            <tr>
                <th width="10%">Hadir (H)</th>
                <th width="10%">Sakit (S)</th>
                <th width="10%">Izin (I)</th>
                <th width="10%">Alpha (A)</th>
                <th width="10%">Dinas Luar (DL)</th>
                <th width="10%">Terlambat (T)</th>
                <th width="10%">Waktu Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data_rekap as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['nama'] }}</td>
                <td class="text-center">{{ $item['total']['H'] }}</td>
                <td class="text-center">{{ $item['total']['S'] }}</td>
                <td class="text-center">{{ $item['total']['I'] ?? 0 }}</td>
                <td class="text-center">{{ $item['total']['A'] ?? 0 }}</td>
                <td class="text-center">{{ $item['total']['DL'] ?? 0 }}</td>
                <td class="text-center">{{ $item['total']['T'] ?? 0 }}</td>
                <td class="text-center">{{ $item['format_terlambat'] ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data {{ $tipe ?? 'siswa' }}.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        {{ $sekolah->kota ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        @if(isset($tipe) && $tipe == 'guru')
        Kepala Sekolah,<br><br><br><br>
        <b>{{ $sekolah->nama_kepsek ?? '..................................' }}</b><br>
        NIP. {{ $sekolah->nip_kepsek ?? '-' }}
        @else
        Wali Kelas {{ $kelas->nama_kelas ?? '' }},<br><br><br><br>
        <b>{{ $kelas->guru->nama_lengkap ?? $kelas->guru->nama_guru ?? '..................................' }}</b><br>
        NIP. {{ $kelas->guru->nik ?? '-' }}
        @endif
    </div>
</body>
</html>
