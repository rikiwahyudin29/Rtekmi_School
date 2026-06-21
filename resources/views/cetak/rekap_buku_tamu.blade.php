<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Buku Tamu</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px; cursor: pointer;">Cetak Laporan</button>
    </div>

    <div class="header">
        <h1>REKAPITULASI BUKU TAMU & KUNJUNGAN</h1>
        <p>{{ $sekolah->nama_sekolah ?? 'Sistem Informasi Sekolah' }}</p>
        <p>
            Periode: {{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }} <br>
            Kategori: {{ $kategori }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="10%">Tanggal</th>
                <th width="10%">Antrean</th>
                <th width="20%">Nama Lengkap</th>
                <th width="15%">Instansi/Asal</th>
                <th width="20%">Keperluan</th>
                <th width="10%">Kategori</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tamu as $index => $t)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y') }}</td>
                    <td class="text-center">#{{ $t->no_antrian }}</td>
                    <td>{{ $t->nama_lengkap }}<br><small>{{ $t->no_hp }}</small></td>
                    <td>{{ $t->instansi_asal ?? '-' }}</td>
                    <td>{{ $t->keperluan }}</td>
                    <td class="text-center">{{ $t->kategori }}</td>
                    <td class="text-center">{{ $t->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data tamu pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; float: right; width: 300px; text-align: center;">
        <p>{{ $sekolah->kabupaten ?? 'Kabupaten' }}, {{ date('d F Y') }}</p>
        <p>Petugas Lobi / Piket</p>
        <br><br><br>
        <p><strong>( ________________________ )</strong></p>
    </div>
</body>
</html>
