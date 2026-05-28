<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nilai Ujian</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12px; margin: 0; padding: 0; }
        .page { width: 21cm; min-height: 29.7cm; padding: 2cm; margin: 1cm auto; border: 1px #D3D3D3 solid; border-radius: 5px; background: white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); }
        .kop { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h2, .kop h3, .kop h4 { margin: 0; padding: 2px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px; text-decoration: underline; text-transform: uppercase; }
        table.info { width: 100%; margin-bottom: 20px; }
        table.info td { vertical-align: top; padding: 3px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px; }
        table.data th { background-color: #f0f0f0; text-align: center; }
        .ttd-box { width: 100%; margin-top: 30px; }
        .ttd-box table { width: 100%; text-align: center; }
        .ttd-box td { padding-top: 60px; }
        
        @media print {
            body { background: none; }
            .page { margin: 0; border: initial; border-radius: initial; width: initial; min-height: initial; box-shadow: initial; background: initial; page-break-after: auto; }
            @page { margin: 1cm; size: A4 portrait; }
            table.data { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="page">
        @php $fileKop = !empty($sekolah->kop_surat) ? $sekolah->kop_surat : 'default_kop.png'; @endphp
        @if(file_exists(public_path('uploads/identitas/' . $fileKop)))
        <div class="kop" style="border-bottom: none;">
            <img src="{{ asset('uploads/identitas/' . $fileKop) }}" style="width: 100%; max-height: 150px; object-fit: contain;">
        </div>
        @else
        <div class="kop">
            <h3>{{ $sekolah->nama_sekolah ?? 'NAMA INSTANSI / SEKOLAH' }}</h3>
            <h4>{{ $sekolah->alamat ?? 'Alamat Instansi / Sekolah' }}</h4>
        </div>
        @endif

        <div class="title">DAFTAR NILAI UJIAN</div>

        <table class="info">
            <tr>
                <td width="15%">Mata Pelajaran</td>
                <td width="35%">: {{ $jadwal->draftUjian->mapel->nama_mapel ?? $jadwal->nama_ujian ?? '-' }}</td>
                <td width="15%">Ruang / Kelas</td>
                <td width="35%">: {{ $jadwal->ruangan->nama_ruangan ?? 'Semua Ruang' }}</td>
            </tr>
            <tr>
                <td>Tanggal Ujian</td>
                <td>: {{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->translatedFormat('d F Y') }}</td>
                <td>Penyusun Soal</td>
                <td>: -</td>
            </tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th rowspan="2" width="3%">No.</th>
                    <th rowspan="2" width="10%">Nomor Induk</th>
                    <th rowspan="2" width="15%">Nama Peserta</th>
                    <th rowspan="2" width="8%">Kelas</th>
                    <th colspan="5">Jumlah Benar (Obyektif)</th>
                    <th rowspan="2" width="8%">Skor Obyektif</th>
                    <th rowspan="2" width="8%">Nilai Esai</th>
                    <th rowspan="2" width="8%">Total Nilai</th>
                </tr>
                <tr>
                    <th width="6%">PG</th>
                    <th width="6%">PG K.</th>
                    <th width="6%">B/S</th>
                    <th width="6%">Menj.</th>
                    <th width="6%">Isian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peserta as $index => $p)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td align="center">{{ $p->siswa->nis ?? '-' }}</td>
                    <td>{{ $p->siswa->nama_lengkap ?? '-' }}</td>
                    <td align="center">{{ $p->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td align="center">{{ $p->pg_benar ?? 0 }}</td>
                    <td align="center">{{ $p->pgmulti_benar ?? 0 }}</td>
                    <td align="center">{{ $p->pgtf_benar ?? 0 }}</td>
                    <td align="center">{{ $p->pgcouple_benar ?? 0 }}</td>
                    <td align="center">{{ $p->shortentry_benar ?? 0 }}</td>
                    <td align="center">{{ number_format($p->nilai_pg ?? 0, 2) }}</td>
                    <td align="center">{{ number_format($p->nilai_esai ?? 0, 2) }}</td>
                    <td align="center" style="font-weight: bold;">{{ number_format(($p->nilai_pg ?? 0) + ($p->nilai_esai ?? 0), 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" align="center">Tidak ada data nilai</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ttd-box">
            <table>
                <tr>
                    <td width="50%"></td>
                    <td width="50%">
                        Guru Mata Pelajaran / Penilai<br><br><br><br>
                        (_________________________)
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
