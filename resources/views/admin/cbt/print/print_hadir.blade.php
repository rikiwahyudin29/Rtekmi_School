<!DOCTYPE html>
<html>
<head>
    <title>Daftar Hadir - {{ $jadwal->nama_ujian ?? 'Ujian' }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; margin: 0; padding: 10px; }
        /* Style untuk Gambar Kop Surat */
        .kop-image { width: 100%; height: auto; margin-bottom: 20px; display: block; }
        
        .judul-doc { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 15px; text-decoration: underline; }
        .info-ujian { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .info-ujian td { padding: 2px 0; font-size: 11px; }
        
        .table-peserta { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-peserta th, .table-peserta td { border: 1px solid #000; padding: 6px; font-size: 11px; }
        .table-peserta th { background: #f2f2f2; text-transform: uppercase; }
        
        .footer-sign { width: 100%; margin-top: 20px; text-align: center; font-size: 11px; }
        .footer-sign td { width: 33.3%; vertical-align: top; }
        .ttd-space { height: 50px; }
        .nama-ttd { font-weight: bold; text-decoration: underline; }
        
        @media print {
            body { padding: 0; }
            @page { margin: 1cm; size: A4; }
        }
    </style>
</head>
<body onload="window.print()">
    @php 
    $fileKop = !empty($sekolah->kop_surat) ? $sekolah->kop_surat : 'default_kop.png'; 
    $hari = \Carbon\Carbon::parse($jadwal->waktu_mulai)->translatedFormat('l');
    $pos = $jadwal->nama_ujian ?? 'CBT';
    @endphp

    @if(file_exists(public_path('uploads/identitas/' . $fileKop)))
        <img src="{{ asset('uploads/identitas/' . $fileKop) }}" class="kop-image">
    @else
        <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px;">
            <h2 style="margin:0;">{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</h2>
            <p style="margin:0;">{{ $sekolah->alamat ?? '' }}</p>
        </div>
    @endif

    <div class="judul-doc">DAFTAR HADIR PESERTA {{ strtoupper($pos) }}</div>

    <table class="info-ujian">
        <tr>
            <td width="18%">SEKOLAH/MADRASAH</td><td width="2%">:</td><td width="35%">{{ strtoupper($sekolah->nama_sekolah ?? '-') }}</td>
            <td width="15%">KODE SEKOLAH</td><td width="2%">:</td><td>{{ $sekolah->npsn ?? '-' }}</td>
        </tr>
        <tr>
            <td>ID SERVER / RUANG</td><td>:</td><td>{{ $jadwal->ruangan->nama_ruangan ?? 'Semua Ruang' }}</td>
            <td>SESI / HARI</td><td>:</td><td>....... / {{ $hari }}</td>
        </tr>
        <tr>
            <td>MATA PELAJARAN</td><td>:</td><td>{{ $jadwal->draftUjian->mapel->nama_mapel ?? $jadwal->nama_ujian ?? '-' }}</td>
            <td>TANGGAL</td><td>:</td><td>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="table-peserta">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="120">Username</th>
                <th>Nama Peserta</th>
                <th width="180">Tanda Tangan</th>
                <th width="100">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $index => $p)
            <tr>
                <td align="center">{{ $index + 1 }}.</td>
                <td align="center"><strong>{{ $p->siswa->nis ?? '-' }}</strong></td>
                <td>{{ strtoupper($p->siswa->nama_lengkap ?? '-') }}</td>
                <td style="position: relative; height: 45px;">
                    <span style="font-size: 8px; position: absolute; top: 2px; left: 2px;">{{ $index + 1 }}.</span>
                    <div style="{{ (($index + 1) % 2 == 0) ? 'text-align: right;' : 'text-align: left;' }} margin-top: 15px; border-bottom: 1px dotted #000; width: 80%; display: inline-block;"></div>
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td>Proktor<br><div class="ttd-space"></div><span class="nama-ttd">{{ $sekolah->nama_proktor ?? '....................' }}</span></td>
            <td>Pengawas<br><div class="ttd-space"></div><span class="nama-ttd">....................</span></td>
            <td>Penanggung Jawab<br><div class="ttd-space"></div><span class="nama-ttd">{{ $sekolah->nama_kepsek ?? '....................' }}</span></td>
        </tr>
    </table>
</body>
</html>
