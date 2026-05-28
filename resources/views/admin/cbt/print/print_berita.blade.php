<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara - {{ $jadwal->nama_ujian ?? 'Ujian' }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; line-height: 1.4; padding: 0; margin: 0; color: #000; }
        .page { padding: 0.5cm 1.5cm; }
        
        /* KOP SURAT GAMBAR */
        .kop-image { width: 100%; height: auto; margin-bottom: 10px; display: block; }
        
        .judul-ba { text-align: center; font-weight: bold; font-size: 15px; margin-bottom: 15px; text-transform: uppercase; line-height: 1.2; }
        .text-isi { text-align: justify; margin-bottom: 15px; text-indent: 30px; }
        
        .section-title { font-weight: bold; margin-bottom: 5px; }
        .data-table { width: 100%; margin-left: 20px; border-collapse: collapse; margin-bottom: 15px; }
        .data-table td { padding: 2px 0; vertical-align: top; }
        
        .catatan-box { border: 1px solid #000; width: 100%; min-height: 100px; padding: 10px; margin-bottom: 20px; }
        
        /* PENATAAN TANDA TANGAN */
        .signature-container { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .signature-container td { vertical-align: top; width: 45%; }
        .sign-row { margin-bottom: 15px; display: flex; align-items: flex-start; }
        .label-sign { width: 120px; display: inline-block; }
        .dot-line { flex-grow: 1; border-bottom: 1px dotted #000; margin-left: 5px; min-width: 150px; }
        
        .ttd-wrapper { text-align: center; width: 150px; }
        .ttd-line { margin-top: 45px; border-bottom: 1px solid #000; font-weight: bold; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    @php 
    $fileKop = !empty($sekolah->kop_surat) ? $sekolah->kop_surat : 'default_kop.png'; 
    $hari = \Carbon\Carbon::parse($jadwal->waktu_mulai)->translatedFormat('l');
    $pos = $jadwal->nama_ujian ?? 'CBT';
    @endphp

    <div class="page">
        @if(file_exists(public_path('uploads/identitas/' . $fileKop)))
            <img src="{{ asset('uploads/identitas/' . $fileKop) }}" class="kop-image">
        @else
            <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px;">
                <h2 style="margin:0;">{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</h2>
                <p style="margin:0;">{{ $sekolah->alamat ?? '' }}</p>
            </div>
        @endif

        <div class="judul-ba">
            BERITA ACARA PELAKSANAAN<br>
            {{ strtoupper($pos) }} TAHUN PELAJARAN {{ $sekolah->tahun_ajaran ?? date('Y') }}/{{ ($sekolah->tahun_ajaran ?? date('Y')) + 1 }}
        </div>

        <p class="text-isi">
            Pada hari ini <strong>{{ $hari }}</strong> tanggal <strong>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('d') }}</strong> bulan <strong>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->translatedFormat('F') }}</strong> tahun <strong>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('Y') }}</strong>, 
            di <strong>{{ strtoupper($sekolah->nama_sekolah ?? '-') }}</strong> telah diselenggarakan {{ $pos }} untuk mata pelajaran <strong>{{ $jadwal->draftUjian->mapel->nama_mapel ?? $jadwal->nama_ujian ?? '-' }}</strong> dari pukul ....... sampai dengan pukul .......
        </p>

        <div class="section-title">1. Identitas Server & Ruang</div>
        <table class="data-table">
            <tr><td width="180">Username</td><td width="10">:</td><td>{{ $sekolah->npsn ?? '-' }}</td></tr>
            <tr><td>Sekolah/Madrasah</td><td>:</td><td>{{ strtoupper($sekolah->nama_sekolah ?? '-') }}</td></tr>
            <tr><td>ID Server / Ruang</td><td>:</td><td>{{ $jadwal->ruangan->nama_ruangan ?? 'Semua Ruang' }}</td></tr>
            <tr><td>Sesi</td><td>:</td><td>.......</td></tr>
            <tr><td>Jumlah Peserta Seharusnya</td><td>:</td><td>{{ $peserta->count() }} Peserta</td></tr>
            <tr><td>Jumlah Hadir (Ikut Ujian)</td><td>:</td><td>....... Peserta</td></tr>
            <tr><td>Jumlah Tidak Hadir</td><td>:</td><td>....... Peserta</td></tr>
            <tr><td>Username Tidak Hadir</td><td>:</td><td>....................................................................</td></tr>
        </table>

        <div class="section-title">2. Catatan selama Tes :</div>
        <div class="catatan-box">
            </div>

        <p style="margin-bottom: 5px;">Yang membuat berita acara:</p>
        
        <table class="signature-container">
            <tr>
                <td>
                    <div class="sign-row">
                        <span class="label-sign">1. Proktor</span> : <span class="dot-line">{{ $sekolah->nama_proktor ?? '........................' }}</span>
                    </div>
                    <div class="sign-row">
                        <span class="label-sign">&nbsp;&nbsp;&nbsp;NIP</span> : <span class="dot-line">{{ $sekolah->nip_proktor ?? '-' }}</span>
                    </div>
                    
                    <div class="sign-row" style="margin-top: 10px;">
                        <span class="label-sign">2. Pengawas</span> : <span class="dot-line">....................................</span>
                    </div>
                    <div class="sign-row">
                        <span class="label-sign">&nbsp;&nbsp;&nbsp;NIP</span> : <span class="dot-line">....................................</span>
                    </div>

                    <div class="sign-row" style="margin-top: 10px;">
                        <span class="label-sign">3. Penanggung Jawab</span> : <span class="dot-line">{{ $sekolah->nama_kepsek ?? '........................' }}</span>
                    </div>
                    <div class="sign-row">
                        <span class="label-sign">&nbsp;&nbsp;&nbsp;NIP</span> : <span class="dot-line">{{ $sekolah->nip_kepsek ?? '-' }}</span>
                    </div>
                </td>
                <td align="center">
                    <div style="font-weight: bold; margin-bottom: 10px;">Tanda Tangan</div>
                    <div style="text-align: left; margin-left: 40px;">
                        1. ........................................<br><br><br>
                        2. ........................................<br><br><br>
                        3. ........................................
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
