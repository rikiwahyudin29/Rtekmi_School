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
    
    @php
        $mapels = $rapor_akhir->pluck('mapel')->unique('id')->filter()->sortBy(function($m) { 
            $u = (int) ($m->urutan ?? 0); 
            return $u === 0 ? 999 : $u; 
        })->values();
    @endphp
    
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">NISN</th>
                <th rowspan="2">Nama Siswa</th>
                <th rowspan="2">L/P</th>
                @if($mapels->count() > 0)
                    <th colspan="{{ $mapels->count() }}">Mata Pelajaran</th>
                @endif
                <th rowspan="2">Jumlah</th>
                <th rowspan="2">Rata-rata</th>
                <th rowspan="2">Peringkat</th>
                <th colspan="3">Kehadiran</th>
            </tr>
            <tr>
                @foreach($mapels as $mapel)
                    <th style="writing-mode: vertical-rl; transform: rotate(180deg); height: 100px; max-width: 30px;">
                        {{ $mapel->singkatan ?? substr($mapel->nama_mapel, 0, 15) }}
                    </th>
                @endforeach
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
                <td>{{ $s->jk == 'P' || $s->jenis_kelamin == 'P' ? 'P' : 'L' }}</td>
                
                @foreach($mapels as $mapel)
                    @php
                        $nilai = $rapor_akhir->where('siswa_id', $s->id)->where('mapel_id', $mapel->id)->first();
                    @endphp
                    <td>{{ $nilai ? $nilai->nilai_akhir : '' }}</td>
                @endforeach
                
                @if(isset($peringkat_data[$s->id]))
                    <td>{{ $peringkat_data[$s->id]['total'] }}</td>
                    <td>{{ number_format($peringkat_data[$s->id]['rata'], 2) }}</td>
                    <td style="font-weight: bold;">{{ $peringkat_data[$s->id]['rank'] }}</td>
                @else
                    <td>0</td><td>0</td><td>-</td>
                @endif
                
                @php $absen = $kehadiran->where('user_id', $s->user_id)->first(); @endphp
                <td>{{ $absen->sakit ?? 0 }}</td>
                <td>{{ $absen->izin ?? 0 }}</td>
                <td>{{ $absen->tanpa_keterangan ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>window.print();</script>
</body>
</html>
