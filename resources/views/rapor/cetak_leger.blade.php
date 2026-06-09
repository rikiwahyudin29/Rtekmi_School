<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Leger Kelas - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; }
        .header { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 5px; text-align: center; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="header">LEGER NILAI KELAS {{ $kelas->nama_kelas }}</div>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">NISN</th>
                <th rowspan="2">Nama Siswa</th>
                <th colspan="3">Kehadiran</th>
                <th rowspan="2">Rata-rata Nilai</th>
            </tr>
            <tr>
                <th>S</th>
                <th>I</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $index => $s)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $s->nisn }}</td>
                <td class="text-left">{{ $s->nama_lengkap }}</td>
                <td>{{ $kehadiran->where('siswa_id', $s->id)->first()->sakit ?? 0 }}</td>
                <td>{{ $kehadiran->where('siswa_id', $s->id)->first()->izin ?? 0 }}</td>
                <td>{{ $kehadiran->where('siswa_id', $s->id)->first()->tanpa_keterangan ?? 0 }}</td>
                <td>
                    @php
                        $nilai = $rapor_akhir->where('siswa_id', $s->id);
                        $avg = $nilai->count() > 0 ? $nilai->avg('nilai_akhir') : 0;
                    @endphp
                    {{ number_format($avg, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>window.print();</script>
</body>
</html>
