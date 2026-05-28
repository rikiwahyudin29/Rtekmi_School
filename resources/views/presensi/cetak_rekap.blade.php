<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kehadiran Bulanan</title>
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
        th { background-color: #f2f2f2; text-align: center; font-size: 10px;}
        td { font-size: 10px; }
        .text-center { text-align: center; }
        .ttd { float: right; width: 300px; text-align: center; margin-top: 30px; }
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
        <p>{{ $sekolah->alamat ?? 'Jl. Contoh Alamat No. 123' }} | Telp: {{ $sekolah->no_telp ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
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
                <th colspan="5">Total Kehadiran</th>
            </tr>
            <tr>
                <th width="10%">Hadir (H)</th>
                <th width="10%">Sakit (S)</th>
                <th width="10%">Izin (I)</th>
                <th width="10%">Alpha (A)</th>
                <th width="10%">Dinas Luar (DL)</th>
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
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data {{ $tipe ?? 'siswa' }}.</td>
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
