<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monitoring Kehadiran PKL - {{ date('d M Y', strtotime($mulai)) }} s/d {{ date('d M Y', strtotime($selesai)) }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; color: #333; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo { width: 80px; }
        .kop-text h3 { margin: 0; font-size: 14pt; }
        .kop-text h1 { margin: 0; font-size: 18pt; font-weight: bold; }
        .kop-text p { margin: 3px 0 0; font-size: 10pt; }
        .title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 11pt; margin-bottom: 20px; }
        .info-table { margin-bottom: 20px; width: 50%; }
        .info-table td { padding: 3px 0; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        .data-table th { background-color: #f0f0f0; }
        .text-left { text-align: left !important; }
        .badge { padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 9pt; }
        .bg-hadir { background-color: #d1fae5; color: #047857; }
        .bg-izin { background-color: #fef3c7; color: #b45309; }
        .bg-sakit { background-color: #ffe4e6; color: #e11d48; }
        .bg-alpha { background-color: #f1f5f9; color: #64748b; }
        .ttd { width: 100%; margin-top: 50px; }
        .ttd-table { width: 100%; border-collapse: collapse; }
        .ttd-table td { width: 50%; text-align: center; vertical-align: bottom; height: 100px; }
    </style>
</head>
<body>
    @php
        $kopSuratBase64 = '';
        if(isset($sekolah->kop_surat) && $sekolah->kop_surat) {
            $pathKop = public_path('uploads/identitas/' . $sekolah->kop_surat);
            if(file_exists($pathKop)) {
                $type = pathinfo($pathKop, PATHINFO_EXTENSION);
                $data = file_get_contents($pathKop);
                $kopSuratBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $logoBase64 = '';
        if(isset($sekolah->logo) && $sekolah->logo) {
            $pathLogo = public_path('uploads/identitas/' . $sekolah->logo);
            if(file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
    @endphp
    
    @if($kopSuratBase64)
        <div style="width: 100%; margin-bottom: 20px; text-align: center;">
            <img src="{{ $kopSuratBase64 }}" style="width: 100%;">
        </div>
    @else
        <div class="header">
            <table>
                <tr>
                    <td style="width: 15%; text-align: center;">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" class="logo">
                        @endif
                    </td>
                    <td style="width: 70%; text-align: center;" class="kop-text">
                        <h3>PEMERINTAH PROVINSI {{ strtoupper($sekolah->provinsi ?? 'JAWA BARAT') }}</h3>
                        <h3>DINAS PENDIDIKAN</h3>
                        <h1>{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</h1>
                        <p>{{ $sekolah->alamat ?? 'Alamat Sekolah' }} - Telp. {{ $sekolah->no_telp ?? '-' }}</p>
                        <p>Website: {{ $sekolah->website ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                    </td>
                    <td style="width: 15%; text-align: center;">
                        <!-- Kosong -->
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @php
        function tanggal_indo($tanggal) {
            $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $split = explode('-', $tanggal);
            return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
        }
    @endphp

    <div class="title">REKAPITULASI KEHADIRAN PKL</div>
    <div class="subtitle">PERIODE: {{ strtoupper(tanggal_indo($mulai)) }} S.D {{ strtoupper(tanggal_indo($selesai)) }}</div>

    <table class="info-table">
        <tr>
            <td style="width: 150px;">Nama Pembimbing</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $guru->nama_lengkap ?? 'Unknown' }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;" class="text-left">Nama Siswa</th>
                <th style="width: 30%;" class="text-left">Mitra DU/DI</th>
                <th style="width: 10%;">Hadir</th>
                <th style="width: 10%;">Sakit</th>
                <th style="width: 10%;">Izin</th>
                <th style="width: 10%;">Alpa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa_binaan as $i => $s)
                @php
                    $rekap = $rekap_absen[$s->id] ?? ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left"><strong>{{ $s->nama_siswa }}</strong><br><span style="font-size: 8pt; color: #666;">NIS: {{ $s->nis }}</span></td>
                    <td class="text-left">{{ $s->nama_dudi }}</td>
                    <td><strong>{{ $rekap['Hadir'] }}</strong></td>
                    <td>{{ $rekap['Sakit'] }}</td>
                    <td>{{ $rekap['Izin'] }}</td>
                    <td>{{ $rekap['Alpa'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data siswa binaan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        <table class="ttd-table">
            <tr>
                <td></td>
                <td>
                    {{ $sekolah->kabupaten ?? 'Cirebon' }}, {{ tanggal_indo(date('Y-m-d')) }}<br>
                    Guru Pembimbing PKL,<br><br><br><br><br>
                    <strong><u>{{ $guru->nama_lengkap ?? '.........................' }}</u></strong><br>
                    NIP. {{ $guru->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
