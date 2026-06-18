<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nilai Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        @page {
            size: {{ request('kertas', 'A4') }};
            margin: {{ request('margin_atas', 20) }}mm {{ request('margin_kanan', 20) }}mm {{ request('margin_bawah', 20) }}mm {{ request('margin_kiri', 20) }}mm;
        }
        body { font-family: 'Times New Roman', Times, serif; margin: 0; padding: 0; background: #fff; font-size: 14px; line-height: 1.4; color: #000; }
        .page { width: 100%; box-sizing: border-box; background: white; padding: 10px; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; page-break-after: always; padding: 0; }
            /* Table Layouts */
            table.report-container { width: 100%; }
        }
        .page:last-child { page-break-after: auto; }
        
        .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 15px; }
        .header-table td { padding: 1px 0; vertical-align: top; }
        .ht-label { width: 15%; }
        .ht-colon { width: 2%; text-align: center; }
        .ht-value { width: 45%; }
        .ht-label-r { width: 15%; }
        .ht-colon-r { width: 2%; text-align: center; }
        .ht-value-r { width: 21%; }

        .title-center { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0; }

        table.bordered { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        table.bordered tr { page-break-inside: avoid; page-break-after: auto; }
        table.bordered th, table.bordered td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        table.bordered th { font-weight: bold; text-align: center; background-color: #f9f9f9; }

        .flex-container { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 15px; page-break-inside: avoid; }
        .flex-item { width: 48%; }
        
        .box-title { text-align: center; font-weight: bold; background-color: #f9f9f9; border-bottom: 1px solid #000; padding: 5px; }
        .box-content { min-height: 60px; padding: 8px; }
        .box-wrapper { border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid; }

        .signature-table { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .signature-table td { text-align: center; vertical-align: top; }
        .sig-space { height: 80px; }
        
        thead.report-header { display: table-header-group; }
        tfoot.report-footer { display: table-footer-group; }
        .report-footer td { height: 30px; }
        .footer-content { position: fixed; bottom: 0; width: 100%; font-size: 11px; font-style: italic; border-top: 1px solid #000; padding-top: 5px; background: white; display: flex; justify-content: space-between; }
        .page-number::after { content: "Halaman " counter(page); }
    </style>
</head>
<body onload="window.print()" style="counter-reset: page;">
    <div class="page">
        <table class="report-container">
            <thead class="report-header">
                <tr><td></td></tr>
            </thead>
            <tfoot class="report-footer">
                <tr>
                    <td>
                        <div class="footer-content">
                            <span>{{ $siswa->nama_lengkap }} | {{ $siswa->kelas->nama_kelas ?? '' }} | Semester {{ $tahun_ajaran && $tahun_ajaran->semester === 'Genap' ? '2' : '1' }} - {{ $tahun_ajaran->tahun_ajaran ?? '' }}</span>
                            <span class="page-number"></span>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td>
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
                                @php
                                    $fase = 'E';
                                    if(isset($siswa->kelas->tingkat)) {
                                        $tingkat = $siswa->kelas->tingkat;
                                        if($tingkat == '11' || $tingkat == '12' || str_contains(strtolower($siswa->kelas->nama_kelas), 'xi')) {
                                            $fase = 'F';
                                        }
                                    } elseif(isset($siswa->kelas->nama_kelas)) {
                                        if(str_contains(strtoupper($siswa->kelas->nama_kelas), 'XI') || str_contains(strtoupper($siswa->kelas->nama_kelas), 'XII')) {
                                            $fase = 'F';
                                        }
                                    }
                                @endphp
                                <td class="ht-value-r">{{ $fase }}</td>
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
                                @php
                                    $sorted_rapor = collect($rapor_akhir)
                                        ->sortBy(function($item) { return strtolower($item->mapel->nama_mapel ?? 'Z'); })
                                        ->sortBy(function($item) { 
                                            $u = (int) ($item->mapel->urutan ?? 0); 
                                            return $u === 0 ? 999 : $u; 
                                        })
                                        ->sortBy(function($item) { return strtolower($item->mapel->kelompok ?? 'Z'); });
                                    $nomor = 1;
                                @endphp
                                @forelse($sorted_rapor as $rapor)
                                    <tr>
                                        <td style="text-align: center; vertical-align: top;">{{ $nomor++ }}</td>
                                        <td style="vertical-align: top;">{{ $rapor->mapel->nama_mapel ?? '-' }}</td>
                                        <td style="text-align: center; vertical-align: top; font-weight: bold;">
                                            {{ $rapor->nilai_akhir }}
                                        </td>
                                        <td style="vertical-align: top; font-size: 11px; text-align: justify; padding-right: 5px;">
                                            <div style="margin-bottom: 5px;">{{ $rapor->deskripsi_tertinggi }}</div>
                                            <div>{{ $rapor->deskripsi_terendah }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center;">Belum ada nilai yang diinput.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- PRAKTIK KERJA LAPANGAN (PKL) -->
                        <table class="bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 30%;">Mitra DU/DI</th>
                                    <th style="width: 25%;">Lokasi</th>
                                    <th style="width: 15%;">Lamanya (Bulan)</th>
                                    <th style="width: 25%;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($pkl) && count($pkl) > 0)
                                    @foreach($pkl as $index => $p)
                                    <tr>
                                        <td style="text-align: center;">{{ $index + 1 }}</td>
                                        <td>{{ $p->dudi->nama_dudi ?? '-' }}</td>
                                        <td>{{ $p->lokasi ?? '-' }}</td>
                                        <td style="text-align: center;">{{ $p->lama_bulan ?? '-' }}</td>
                                        <td>{{ $p->keterangan ?? '-' }} (Nilai: {{ $p->nilai ?? '-' }})</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" style="text-align: center;">-</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

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
                                        <td>{{ $ek->deskripsi_dapodik ?? 'Baik' }}</td>
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
                        <div class="box-wrapper" style="padding: 10px; font-weight: bold;">
                            Keterangan Kenaikan Kelas : 
                            @if(isset($kenaikan))
                                @if($kenaikan->status === 'Naik')
                                    Naik ke kelas {{ $kenaikan->kelasTujuan->nama_kelas ?? '.......' }}
                                @elseif($kenaikan->status === 'Tidak Naik')
                                    Tidak Naik Kelas
                                @elseif($kenaikan->status === 'Lulus')
                                    Lulus
                                @elseif($kenaikan->status === 'Tidak Lulus')
                                    Tidak Lulus
                                @else
                                    Naik/Tidak Naik ke kelas .......
                                @endif
                            @else
                                Naik/Tidak Naik ke kelas .......
                            @endif
                        </div>
                        @endif


                        <!-- TANGGAPAN ORANG TUA -->
                        <div class="box-wrapper">
                            <div class="box-title">Tanggapan Orang Tua/Wali Murid</div>
                            <div class="box-content" style="min-height: 80px;">
                                
                            </div>
                        </div>

                        <!-- SIGNATURES -->
                        @php
                            if (request('tanggal_titimangsa')) {
                                $tgl_ttd = \Carbon\Carbon::parse(request('tanggal_titimangsa'))->locale('id')->translatedFormat('d F Y');
                            } else {
                                $tgl_ttd = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
                            }
                            
                            $wk = $siswa->kelas->waliKelas ?? null;
                            $nama_wk = 'Nama Wali Kelas';
                            if ($wk) {
                                $gelar_depan = $wk->gelar_depan ? $wk->gelar_depan . ' ' : '';
                                $gelar_belakang = $wk->gelar_belakang ? ', ' . $wk->gelar_belakang : '';
                                $nama_wk = trim($gelar_depan . $wk->nama_lengkap . $gelar_belakang);
                            }
                        @endphp
                        @if(request('posisi_ttd_ks', 'Dibawah Wali Kelas') === 'Sejajar Wali Kelas')
                        <table class="signature-table" style="width: 100%;">
                            <tr>
                                <td style="width: 30%; text-align: center; vertical-align: top;">
                                    <br>
                                    Mengetahui,<br>
                                    Orang Tua Murid
                                    <div class="sig-space"></div>
                                    (..........................................)
                                </td>
                                <td style="width: 40%; text-align: center; vertical-align: top;">
                                    <br><br>
                                    Kepala Sekolah
                                    <div class="sig-space"></div>
                                    <div style="display: inline-block; text-align: left;">
                                        <strong><u>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</u></strong><br>
                                        NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                                    </div>
                                </td>
                                <td style="width: 30%; text-align: center; vertical-align: top;">
                                    {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $tgl_ttd }}<br>
                                    Wali Kelas
                                    <div class="sig-space"></div>
                                    <div style="display: inline-block; text-align: left;">
                                        <strong><u>
                                        @if(request('tampil_nama_wali', 'Isi Nama Wali Kelas') === 'Kosongkan Nama Wali')
                                            ..........................................
                                        @else
                                            {{ $nama_wk }}
                                        @endif
                                        </u></strong><br>
                                        @if(request('tampil_nama_wali', 'Isi Nama Wali Kelas') !== 'Kosongkan Nama Wali')
                                            NIP. {{ $wk->nip ?? '-' }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                        @else
                        <table class="signature-table" style="width: 100%;">
                            <tr>
                                <td style="width: 35%; text-align: center; vertical-align: top;">
                                    <br>
                                    Mengetahui,<br>
                                    Orang Tua Murid
                                    <div class="sig-space"></div>
                                    (..........................................)
                                </td>
                                <td style="width: 30%;"></td>
                                <td style="width: 35%; text-align: center; vertical-align: top;">
                                    {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $tgl_ttd }}<br>
                                    Wali Kelas
                                    <div class="sig-space"></div>
                                    <div style="display: inline-block; text-align: left;">
                                        <strong><u>
                                        @if(request('tampil_nama_wali', 'Isi Nama Wali Kelas') === 'Kosongkan Nama Wali')
                                            ..........................................
                                        @else
                                            {{ $nama_wk }}
                                        @endif
                                        </u></strong><br>
                                        @if(request('tampil_nama_wali', 'Isi Nama Wali Kelas') !== 'Kosongkan Nama Wali')
                                            NIP. {{ $wk->nip ?? '-' }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: center; padding-top: 20px;">
                                    Mengetahui,<br>
                                    Kepala Sekolah
                                    <div class="sig-space"></div>
                                    <div style="display: inline-block; text-align: left;">
                                        <strong><u>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</u></strong><br>
                                        NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</body>
</html>
