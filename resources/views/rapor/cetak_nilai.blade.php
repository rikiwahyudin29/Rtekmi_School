<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nilai Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; margin: 0; padding: 0; }
        .page { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.5cm; box-sizing: border-box; background: white; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; margin: 0; width: 100%; height: 100vh; padding: 1.5cm; page-break-after: always; }
        }
        
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { padding: 3px 0; vertical-align: top; }
        .ht-label { width: 15%; }
        .ht-colon { width: 2%; text-align: center; }
        .ht-value { width: 45%; }
        .ht-label-r { width: 15%; }
        .ht-colon-r { width: 2%; text-align: center; }
        .ht-value-r { width: 21%; }

        .title-center { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0; }

        table.bordered { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.bordered th, table.bordered td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        table.bordered th { font-weight: bold; text-align: center; background-color: #f9f9f9; }

        .flex-container { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 15px; }
        .flex-item { width: 48%; }
        
        .box-title { text-align: center; font-weight: bold; background-color: #f9f9f9; border-bottom: 1px solid #000; padding: 5px; }
        .box-content { min-height: 60px; padding: 8px; }
        .box-wrapper { border: 1px solid #000; margin-bottom: 15px; }

        .signature-table { width: 100%; margin-top: 30px; }
        .signature-table td { text-align: center; vertical-align: top; }
        .sig-space { height: 80px; }
        
        .footer { position: fixed; bottom: 1.5cm; left: 1.5cm; right: 1.5cm; font-size: 10px; font-style: italic; border-top: 1px solid #000; padding-top: 5px; display: flex; justify-content: space-between; }
        @media print {
            .footer { position: fixed; bottom: 1.5cm; left: 1.5cm; right: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="ht-label">Nama Murid</td>
                <td class="ht-colon">:</td>
                <td class="ht-value">{{ strtoupper($siswa->nama_lengkap) }}</td>
                <td class="ht-label-r">Kelas</td>
                <td class="ht-colon-r">:</td>
                <td class="ht-value-r">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <td class="ht-label">NIS/NISN</td>
                <td class="ht-colon">:</td>
                <td class="ht-value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</td>
                <td class="ht-label-r">Fase</td>
                <td class="ht-colon-r">:</td>
                <td class="ht-value-r">E</td>
            </tr>
            <tr>
                <td class="ht-label">Sekolah</td>
                <td class="ht-colon">:</td>
                <td class="ht-value">{{ strtoupper($sekolah->nama_sekolah ?? 'SMK BISA') }}</td>
                <td class="ht-label-r">Semester</td>
                <td class="ht-colon-r">:</td>
                <td class="ht-value-r">{{ $tahun_ajaran && $tahun_ajaran->semester === 'Genap' ? '2' : '1' }}</td>
            </tr>
            <tr>
                <td class="ht-label">Alamat</td>
                <td class="ht-colon">:</td>
                <td class="ht-value">{{ strtoupper($sekolah->alamat ?? '-') }}</td>
                <td class="ht-label-r">Tahun Ajaran</td>
                <td class="ht-colon-r">:</td>
                <td class="ht-value-r">{{ $tahun_ajaran->tahun_ajaran ?? '2025/2026' }}</td>
            </tr>
        </table>

        <!-- TITLE -->
        <div class="title-center">LAPORAN HASIL BELAJAR</div>

        <!-- TABEL NILai -->
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Mata Pelajaran</th>
                    <th style="width: 15%;">Nilai Akhir</th>
                    <th style="width: 50%;">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapor_akhir as $index => $rapor)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $rapor->mapel->nama_mapel ?? '-' }}</td>
                    <td style="text-align: center;">{{ $rapor->nilai_akhir }}</td>
                    <td>
                        <div style="margin-bottom: 5px;">{{ $rapor->deskripsi_tertinggi }}</div>
                        <div>{{ $rapor->deskripsi_terendah }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data nilai</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- KOKURIKULER -->
        <div class="box-wrapper">
            <div class="box-title">Kokurikuler</div>
            <div class="box-content">
                <!-- Data P5 / Kokurikuler bisa ditampilkan di sini jika ada -->
            </div>
        </div>

        <!-- EKSTRAKURIKULER -->
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Ekstrakurikuler</th>
                    <th style="width: 50%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($ekskul) && count($ekskul) > 0)
                    @foreach($ekskul as $index => $ek)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $ek->ekskul->nama_ekskul ?? '-' }}</td>
                        <td>{{ $ek->keterangan ?? 'Baik' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- KEHADIRAN & CATATAN -->
        <div class="flex-container">
            <div class="flex-item" style="width: 40%;">
                <table class="bordered" style="margin-bottom: 0; height: 100%;">
                    <thead>
                        <tr>
                            <th colspan="3">Ketidakhadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width: 50%;">Sakit</td>
                            <td style="width: 10%; text-align: center;">:</td>
                            <td style="width: 40%;">{{ $kehadiran->sakit ?? '-' }} hari</td>
                        </tr>
                        <tr>
                            <td>Izin</td>
                            <td style="text-align: center;">:</td>
                            <td>{{ $kehadiran->izin ?? '-' }} hari</td>
                        </tr>
                        <tr>
                            <td>Tanpa Keterangan</td>
                            <td style="text-align: center;">:</td>
                            <td>{{ $kehadiran->tanpa_keterangan ?? '-' }} hari</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex-item" style="width: 58%;">
                <div class="box-wrapper" style="margin-bottom: 0; height: 100%; display: flex; flex-direction: column;">
                    <div class="box-title">Catatan Wali Kelas</div>
                    <div class="box-content" style="flex: 1;">
                        {{ $catatan->catatan ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- KENAIKAN KELAS (Tampil di Genap) -->
        @if($tahun_ajaran && $tahun_ajaran->semester === 'Genap')
        <div class="box-wrapper" style="padding: 10px; text-align: center; font-weight: bold;">
            Keterangan Kenaikan Kelas : Naik/Tidak Naik ke kelas .......
            <!-- Di implementasi riil, bisa diganti dengan field status_naik dari database -->
        </div>
        @endif

        <!-- TANGGAPAN ORANG TUA -->
        <div class="box-wrapper">
            <div class="box-title">Tanggapan Orang Tua/Wali Murid</div>
            <div class="box-content" style="min-height: 80px;">
                
            </div>
        </div>

        <!-- SIGNATURES -->
        <table class="signature-table">
            <tr>
                <td style="width: 33%; text-align: left;">
                    <br>
                    Orang Tua Murid
                    <div class="sig-space"></div>
                    ..........................................
                </td>
                <td style="width: 33%;">
                    <br><br>
                    Kepala Sekolah
                    <div class="sig-space"></div>
                    <strong><u>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</u></strong><br>
                    NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                </td>
                <td style="width: 33%; text-align: left; padding-left: 30px;">
                    {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $tanggal_rapor ?? \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Wali Kelas
                    <div class="sig-space"></div>
                    <strong><u>{{ $siswa->kelas->waliKelas->nama_lengkap ?? 'Nama Wali Kelas' }}</u></strong><br>
                    NIP. {{ $siswa->kelas->waliKelas->nip ?? '-' }}
                </td>
            </tr>
        </table>

        <!-- FOOTER -->
        <div class="footer">
            <div>{{ $siswa->kelas->nama_kelas ?? '-' }} | {{ strtoupper($siswa->nama_lengkap) }} | {{ $siswa->nisn }}</div>
            <div>Halaman : 1</div>
        </div>
    </div>
</body>
</html>
