<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Matrix Bulanan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop img { height: 70px; position: absolute; left: 20px; }
        .kop h2 { margin: 0; font-size: 14px; }
        .kop h3 { margin: 5px 0; font-size: 18px; }
        .kop p { margin: 0; font-size: 10px; }
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
        }
    </style>
</head>
<body onload="window.print()">
    <div class="kop">
        @if($sekolah && $sekolah->logo_sekolah)
            <img src="{{ public_path('uploads/sekolah/' . $sekolah->logo_sekolah) }}" alt="Logo">
        @endif
        <h2>PEMERINTAH PROVINSI {{ strtoupper($sekolah->provinsi ?? 'JAWA BARAT') }}</h2>
        <h3>{{ strtoupper($sekolah->nama_sekolah ?? 'SMK NEGERI 1 CONTOH') }}</h3>
        <p>{{ $sekolah->alamat ?? 'Jl. Contoh Alamat No. 123' }}</p>
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
                <th>{{ substr($dateStr, 8, 2) }}</th>
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
                    if($val == 'H') { $color = 'background-color: #22c55e; color: white; font-weight: bold;'; $totH++; }
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
