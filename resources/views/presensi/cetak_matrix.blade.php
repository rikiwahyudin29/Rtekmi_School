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
    <title>Cetak Rekap Matrix Bulanan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 20px; text-align: center; }
        .kop-surat img.img-kop { width: 100%; height: auto; } 
        .kop-surat-inner { border-bottom: 3px solid #000; padding-bottom: 10px; display: flex; width: 100%; align-items: center; border-bottom: 3px solid #000; margin-bottom: 2px;}
        .kop-logo { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; }
        .kop-text { text-align: center; flex: 1; }
        .kop-text h1, .kop-text h2, .kop-text h3 { margin: 0; font-weight: bold; }
        .kop-text h2 { font-size: 14px; }
        .kop-text h1 { font-size: 18px; margin: 5px 0; }
        .kop-text p { margin: 0; font-size: 10px; }
        .judul { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .info { margin-bottom: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 3px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; font-size: 9px; }
        td { font-size: 9px; text-align: center; }
        .text-left { text-align: left; }
        .ttd { float: right; width: 250px; text-align: center; margin-top: 30px; font-size: 11px;}
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
                    <p>{{ $sekolah->alamat ?? 'Jl. Contoh Alamat No. 123' }} | Website: {{ $sekolah->website ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="judul">
        MATRIX REKAPITULASI KEHADIRAN {{ strtoupper($tipe ?? 'SISWA') }} BULANAN
    </div>

    <div class="info">
        Bulan : {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }} <br>
        @if(isset($tipe) && $tipe == 'guru')
            Divisi : GURU & STAFF
        @else
            Kelas : {{ $kelas->nama_kelas }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="2%">No</th>
                <th rowspan="2" width="15%" class="text-left">Nama Lengkap</th>
                <th colspan="{{ count($dates) }}">Tanggal (19-18)</th>
                <th colspan="6">Total</th>
            </tr>
            <tr>
                @foreach($dates as $dateStr)
                @php 
                    $isLibur = false;
                    if(isset($info_libur) && isset($info_libur[$dateStr])) {
                        $isLibur = true;
                    }
                @endphp
                <th style="{{ $isLibur ? 'background-color: #fca5a5; color: #991b1b;' : '' }}">{{ substr($dateStr, 8, 2) }}</th>
                @endforeach
                <th width="2%">H</th>
                <th width="2%">T</th>
                <th width="2%">S</th>
                <th width="2%">I</th>
                <th width="2%">A</th>
                <th width="8%">Waktu Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $index => $s)
            @php
                $totH = 0; $totT = 0; $totS = 0; $totI = 0; $totA = 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $s->nama_lengkap ?? $s->nama_guru }}</td>
                @foreach($dates as $dateStr)
                @php
                    $val = $matrix[$s->id][$dateStr] ?? '-';
                    $color = '';
                    $isLibur = false;
                    if(isset($info_libur) && isset($info_libur[$dateStr])) {
                        $isLibur = true;
                    }
                    
                    if($isLibur && $val == '-') { 
                        $color = 'background-color: #fecaca; color: #ef4444; font-weight: bold; font-size: 8px;';
                        $val = 'L'; // Tanda libur
                    } elseif($val == 'H') { $color = 'background-color: #22c55e; color: white; font-weight: bold;'; $totH++; }
                    elseif($val == 'T') { $color = 'background-color: #f97316; color: white; font-weight: bold;'; $totT++; $totH++; }
                    elseif($val == 'S') { $color = 'background-color: #3b82f6; color: white; font-weight: bold;'; $totS++; }
                    elseif($val == 'I') { $color = 'background-color: #06b6d4; color: white; font-weight: bold;'; $totI++; }
                    elseif($val == 'DL') { $color = 'background-color: #a855f7; color: white; font-weight: bold;'; $totI++; } // Dinas luar digabung izin
                    elseif($val == 'A') { $color = 'background-color: #ef4444; color: white; font-weight: bold;'; $totA++; }
                    elseif($val == '-') { $color = 'color: #ccc;'; }
                @endphp
                <td style="{{ $color }}">{{ $val }}</td>
                @endforeach
                <td style="font-weight: bold; background-color: #22c55e; color: white;">{{ $totH }}</td>
                <td style="font-weight: bold; background-color: #f97316; color: white;">{{ $totT }}</td>
                <td style="font-weight: bold; background-color: #3b82f6; color: white;">{{ $totS }}</td>
                <td style="font-weight: bold; background-color: #06b6d4; color: white;">{{ $totI }}</td>
                <td style="font-weight: bold; background-color: #ef4444; color: white;">{{ $totA }}</td>
                @php
                    $menit = $data_menit[$s->id] ?? 0;
                    $jam_t = floor($menit / 60);
                    $menit_t = $menit % 60;
                    $format = ($jam_t > 0 ? $jam_t . 'j ' : '') . ($menit_t > 0 || $jam_t == 0 ? $menit_t . 'm' : '');
                @endphp
                <td style="font-weight: bold; background-color: #f97316; color: white;">{{ $format }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd">
        {{ $sekolah->kota ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        @if(isset($tipe) && $tipe == 'guru')
        Kepala Sekolah,<br><br><br><br>
        <b>{{ $sekolah->nama_kepsek ?? '..................................' }}</b><br>
        NIP. {{ $sekolah->nip_kepsek ?? '-' }}
        @else
        Wali Kelas {{ $kelas->nama_kelas }},<br><br><br><br>
        <b>{{ $kelas->guru->nama_lengkap ?? $kelas->guru->nama_guru ?? '..................................' }}</b><br>
        NIP. {{ $kelas->guru->nik ?? '-' }}
        @endif
    </div>
</body>
</html>
