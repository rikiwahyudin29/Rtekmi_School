<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Pelajaran</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop h2 { margin: 5px 0 0; font-size: 16px; font-weight: normal; }
        .kop p { margin: 5px 0 0; font-size: 12px; }
        
        .info { margin-bottom: 15px; }
        .info table { border: none; }
        .info table td { padding: 2px 5px; font-weight: bold; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        .table-data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .ttd { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .ttd table { width: 100%; border: none; text-align: center; }
    </style>
</head>
<body onload="window.print()">

    <div class="kop">
        <h1>DAFTAR JADWAL PELAJARAN</h1>
        <h2>TAHUN AJARAN {{ $tahunAktif ? $tahunAktif->tahun_ajaran : '-' }}</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Laporan</td>
                <td>:</td>
                <td>{{ $judul }}</td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>:</td>
                <td>{{ $subJudul }}</td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">HARI</th>
                <th width="15%">WAKTU</th>
                <th width="20%">MATA PELAJARAN</th>
                <th width="15%">KELAS</th>
                <th width="30%">GURU PENGAJAR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwal as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->hari }}</td>
                <td style="text-align: center;">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</td>
                <td>{{ $item->mapel->nama_mapel ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $item->guru->nama_lengkap ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 15px;">Tidak ada jadwal pelajaran.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        <table>
            <tr>
                <td width="50%"></td>
                <td width="50%">
                    <p>Mengetahui,<br>Wakasek Kurikulum</p>
                    <br><br><br><br>
                    <p><strong>______________________</strong><br>NIP.</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
