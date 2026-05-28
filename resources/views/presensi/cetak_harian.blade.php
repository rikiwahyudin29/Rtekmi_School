<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Kehadiran Harian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop img { height: 80px; position: absolute; left: 20px; }
        .kop h2 { margin: 0; font-size: 18px; }
        .kop h3 { margin: 5px 0; font-size: 22px; }
        .kop p { margin: 0; font-size: 12px; }
        .judul { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .ttd { float: right; width: 300px; text-align: center; margin-top: 30px; }
        @media print {
            @page { size: landscape; }
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
        LAPORAN {{ strtoupper($tipe ?? 'SISWA') }} BERMASALAH (TIDAK HADIR)<br>
        Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama {{ ucfirst($tipe ?? 'Siswa') }}</th>
                <th width="15%">Kelas/Divisi</th>
                <th width="15%">Status</th>
                <th width="35%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['nama_lengkap'] }}</td>
                <td class="text-center">{{ $item['nama_kelas'] }}</td>
                <td class="text-center">{{ $item['status_kehadiran'] }}</td>
                <td>{{ $item['keterangan'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Semua {{ $tipe ?? 'siswa' }} hadir atau belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        {{ $sekolah->kota ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
        Kepala Sekolah,<br><br><br><br>
        <b>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</b><br>
        NIP. {{ $sekolah->nip_kepsek ?? '-' }}
    </div>
</body>
</html>
