<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kehadiran Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop img { height: 80px; position: absolute; left: 20px; }
        .kop h2 { margin: 0; font-size: 16px; }
        .kop h3 { margin: 5px 0; font-size: 20px; }
        .kop p { margin: 0; font-size: 11px; }
        .judul { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .info { margin-bottom: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        td { text-align: center; }
        .text-left { text-align: left; }
        .ttd { float: right; width: 300px; text-align: center; margin-top: 30px; }
        @media print {
            @page { size: portrait; margin: 1cm; }
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
        <p>{{ $sekolah->alamat ?? 'Jl. Contoh Alamat No. 123' }} | Telp: {{ $sekolah->no_telp ?? '-' }}</p>
    </div>

    <div class="judul">
        REKAPITULASI KEHADIRAN INDIVIDU
    </div>

    <table style="border:none; margin-bottom: 15px;">
        <tr style="border:none;">
            <td style="border:none; text-align:left; width: 15%;"><b>Nama Siswa</b></td>
            <td style="border:none; text-align:left; width: 2%;">:</td>
            <td style="border:none; text-align:left;">{{ $siswa->nama_lengkap }}</td>
            <td style="border:none; text-align:left; width: 15%;"><b>Bulan</b></td>
            <td style="border:none; text-align:left; width: 2%;">:</td>
            <td style="border:none; text-align:left;">{{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}</td>
        </tr>
        <tr style="border:none;">
            <td style="border:none; text-align:left;"><b>NIS</b></td>
            <td style="border:none; text-align:left;">:</td>
            <td style="border:none; text-align:left;">{{ $siswa->nis }}</td>
            <td style="border:none; text-align:left;"><b>Kelas</b></td>
            <td style="border:none; text-align:left;">:</td>
            <td style="border:none; text-align:left;">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
    </table>

    <table style="width: 50%; margin: 0 auto 20px auto;">
        <thead>
            <tr>
                <th colspan="2">Rangkuman Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-left" style="width:70%;">Hadir (H)</td><td>{{ $total['H'] }} Hari</td></tr>
            <tr><td class="text-left">Sakit (S)</td><td>{{ $total['S'] }} Hari</td></tr>
            <tr><td class="text-left">Izin (I)</td><td>{{ $total['I'] }} Hari</td></tr>
            <tr><td class="text-left">Alpha (A)</td><td>{{ $total['A'] }} Hari</td></tr>
            <tr><td class="text-left">Terlambat (T)</td><td>{{ $total['T'] }} Kali @if($format_terlambat) ({{ $format_terlambat }}) @endif</td></tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th width="10%">Tanggal</th>
                <th width="30%">Status</th>
                <th width="60%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dates as $dateStr)
                @php 
                    $tglStr = \Carbon\Carbon::parse($dateStr)->translatedFormat('d M Y');
                    $status = $map[$dateStr] ?? '-';
                @endphp
                <tr>
                    <td>{{ $tglStr }}</td>
                    <td>{{ $status }}</td>
                    <td class="text-left">
                        @if($status == '-')
                            Hari Libur / Mendatang
                        @elseif($status == 'Alpha')
                            Tanpa Keterangan
                        @else
                            Tercatat
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd">
        {{ $sekolah->kota ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        Mengetahui,<br>
        Wali Kelas {{ $siswa->kelas->nama_kelas ?? '-' }},<br><br><br><br>
        <b>{{ $siswa->kelas->guru->nama_lengkap ?? $siswa->kelas->guru->nama_guru ?? '..................................' }}</b><br>
        NIP. {{ $siswa->kelas->guru->nik ?? '-' }}
    </div>
</body>
</html>
