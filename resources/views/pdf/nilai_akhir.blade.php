<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nilai Akhir {{ $kelas->nama_kelas }} - {{ $mapel->nama_mapel }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
        }
        th {
            background-color: #f3f4f6;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <h2 style="margin-bottom: 5px;">DATA NILAI AKHIR</h2>
        <h3 style="margin-top: 0; margin-bottom: 5px;">Mata Pelajaran: {{ $mapel->nama_mapel }}</h3>
        <p style="margin-top: 0; margin-bottom: 15px;">Kelas: {{ $kelas->nama_kelas }} | Tahun Ajaran: {{ $tahun_ajaran->tahun_ajaran ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 10%;">NISN</th>
                <th rowspan="2" style="width: 15%;">Nama Siswa</th>
                
                @if(count($tps) > 0)
                <th colspan="{{ count($tps) }}">Nilai Formatif (Per TP)</th>
                @endif
                
                <th colspan="2">Nilai Sumatif</th>
                <th rowspan="2" style="width: 5%;">Nilai Akhir</th>
                <th rowspan="2" style="width: 25%;">Deskripsi Capaian</th>
            </tr>
            <tr>
                @foreach($tps as $tp)
                <th>{{ $tp->kode_tp }}</th>
                @endforeach
                <th>SAS</th>
                <th>STS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $index => $s)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $s->nisn }}</td>
                <td>{{ $s->nama_lengkap }}</td>
                
                @foreach($tps as $tp)
                @php
                    $f = $detail_nilai[$s->id]['formatif']->where('tp_id', $tp->id)->first();
                @endphp
                <td class="text-center">{{ $f ? $f->nilai : '-' }}</td>
                @endforeach
                
                <td class="text-center">{{ $detail_nilai[$s->id]['sas'] }}</td>
                <td class="text-center">{{ $detail_nilai[$s->id]['sts'] }}</td>
                
                @php
                    $akhir = $rapor_akhir->get($s->id);
                @endphp
                <td class="text-center font-bold">{{ $akhir ? $akhir->nilai_akhir : '-' }}</td>
                <td>{{ $akhir ? $akhir->deskripsi_tertinggi : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
