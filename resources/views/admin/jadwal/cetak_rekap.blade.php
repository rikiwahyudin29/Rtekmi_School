<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Jam Mengajar</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop h2 { margin: 5px 0 0; font-size: 16px; font-weight: normal; }
        
        .info { margin-bottom: 15px; }
        .info table { border: none; }
        .info table td { padding: 2px 5px; font-weight: bold; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
        .table-data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .ttd { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .ttd table { width: 100%; border: none; text-align: center; }
    </style>
</head>
<body onload="window.print()">

    <div class="kop">
        <h1>REKAPITULASI JAM MENGAJAR GURU</h1>
        <h2>TAHUN AJARAN {{ $tahunAktif ? $tahunAktif->tahun_ajaran : '-' }}</h2>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="25%">NAMA GURU / NIP</th>
                <th width="25%">KELAS YANG DIAJAR</th>
                <th width="15%">DURASI ASLI</th>
                <th width="15%">ESTIMASI (40 Menit)</th>
                <th width="15%">ESTIMASI (45 Menit)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapGuru as $item)
            <tr>
                <td style="text-align: center;">{{ $no++ }}</td>
                <td>
                    <strong>{{ $item['nama'] }}</strong><br>
                    NIP: {{ $item['nip'] ?: '-' }}
                </td>
                <td>
                    {{ implode(', ', $item['kelas_ajar']) }}
                </td>
                <td style="text-align: center;">
                    {{ $item['jam_asli'] }}<br>
                    <small>({{ $item['total_menit'] }} Menit)</small>
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $item['total_jp_40'] }} JP</td>
                <td style="text-align: center; font-weight: bold;">{{ $item['total_jp_45'] }} JP</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 15px;">Belum ada data rekap mengajar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        <table>
            <tr>
                <td width="33%"></td>
                <td width="33%"></td>
                <td width="33%">
                    <p>Mengetahui,<br>Kepala Sekolah</p>
                    <br><br><br><br>
                    <p><strong>______________________</strong><br>NIP.</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
