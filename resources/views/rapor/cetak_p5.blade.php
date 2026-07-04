<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rapor P5 - {{ $siswa->nama_lengkap }}</title>
    <style nonce="{{ $cspNonce ?? '' }}">
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .header { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 20px; }
        table { w-full border-collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA</div>
    <table style="border: none; margin-bottom: 20px;">
        <tr style="border: none;">
            <td style="border: none; width: 20%;">Nama Peserta Didik</td>
            <td style="border: none; width: 2%;">:</td>
            <td style="border: none; width: 78%;"><b>{{ $siswa->nama_lengkap }}</b></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none;">NISN</td>
            <td style="border: none;">:</td>
            <td style="border: none;">{{ $siswa->nisn }}</td>
        </tr>
        <tr style="border: none;">
            <td style="border: none;">Kelas</td>
            <td style="border: none;">:</td>
            <td style="border: none;">{{ $siswa->kelas->nama_kelas }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-center">Projek & Dimensi</th>
                <th colspan="4" class="text-center">Capaian</th>
            </tr>
            <tr>
                <th class="text-center">MB</th>
                <th class="text-center">SB</th>
                <th class="text-center">BSH</th>
                <th class="text-center">SAB</th>
            </tr>
        </thead>
        <tbody>
            @forelse($nilai_p5 as $n)
            <tr>
                <td>{{ $n->subElemen->elemen->dimensi->nama_dimensi ?? 'Dimensi' }} - {{ $n->subElemen->deskripsi_sub_elemen ?? 'Sub Elemen' }}</td>
                <td class="text-center">{{ $n->nilai == 'MB' ? '✓' : '' }}</td>
                <td class="text-center">{{ $n->nilai == 'SB' ? '✓' : '' }}</td>
                <td class="text-center">{{ $n->nilai == 'BSH' ? '✓' : '' }}</td>
                <td class="text-center">{{ $n->nilai == 'SAB' ? '✓' : '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada data nilai P5</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>window.print();</script>
</body>
</html>
