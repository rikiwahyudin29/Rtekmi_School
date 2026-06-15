<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nilai Rapor Masal</title>
    <style>
        @page {
            size: {{ request('kertas', 'A4') }};
            margin: {{ request('margin_atas', 20) }}mm {{ request('margin_kanan', 20) }}mm {{ request('margin_bawah', 20) }}mm {{ request('margin_kiri', 20) }}mm;
        }
        body { font-family: 'Times New Roman', Times, serif; margin: 0; padding: 0; background: #fff; font-size: 14px; line-height: 1.4; color: #000; }
        .page { width: 100%; box-sizing: border-box; background: white; page-break-after: always; padding: 10px; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; page-break-after: always; padding: 0; }
            .report-container { min-height: 100vh; }
        }
        .page:last-child { page-break-after: auto; }
        
        /* Table Layouts */
        table.report-container { width: 100%; }
        thead.report-header { display: table-header-group; }
        
        .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 15px; }
        .header-table td { padding: 1px 0; vertical-align: top; }
        .ht-label { width: 15%; }
        .ht-colon { width: 2%; text-align: center; }
        .ht-value { width: 45%; }
        .ht-label-r { width: 15%; }
        .ht-colon-r { width: 2%; text-align: center; }
        .ht-value-r { width: 21%; }

        .title-center { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; }

        .bordered { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .bordered th, .bordered td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        .bordered th { background-color: #f0f0f0; text-align: center; font-weight: bold; }

        .box-wrapper { border: 1px solid #000; margin-bottom: 15px; }
        .box-title { background-color: #f0f0f0; border-bottom: 1px solid #000; padding: 4px 8px; font-weight: bold; }
        .box-content { padding: 8px; min-height: 40px; }

        .flex-container { display: flex; gap: 15px; margin-bottom: 15px; }
        .flex-item { flex: 1; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .signature-table td { vertical-align: top; text-align: center; padding: 0; }
        .sig-space { height: 70px; }

    </style>
</head>
<body onload="window.print()">
    @foreach($siswas as $siswa)
    @php
        $rapor_akhir = $rapor_akhir_all[$siswa->id] ?? collect();
        $kehadiran = $kehadiran_all[$siswa->id] ?? (object) ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
        $catatan = $catatan_all[$siswa->id] ?? (object) ['catatan' => ''];
        $ekskul = $ekskul_all[$siswa->id] ?? collect();
    @endphp
    
    <div class="page">
        <table class="report-container">
            <thead class="report-header">
                <tr><td></td></tr>
            </thead>
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
                                        <td colspan="4" style="text-align: center;">Belum ada data nilai</td>
                                    </tr>
                                @endforelse
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
                        @php $kenaikan = $kenaikan_all[$siswa->id] ?? null; @endphp
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
                                    ..........................................
                                </td>
                                <td style="width: 40%; text-align: center; vertical-align: top;">
                                    <br><br>
                                    Kepala Sekolah
                                    <div class="sig-space"></div>
                                    <strong><u>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</u></strong><br>
                                    NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                                </td>
                                <td style="width: 30%; text-align: center; vertical-align: top;">
                                    {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $tgl_ttd }}<br>
                                    Wali Kelas
                                    <div class="sig-space"></div>
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
                                    ..........................................
                                </td>
                                <td style="width: 30%;"></td>
                                <td style="width: 35%; text-align: center; vertical-align: top;">
                                    {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $tgl_ttd }}<br>
                                    Wali Kelas
                                    <div class="sig-space"></div>
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
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: center; padding-top: 20px;">
                                    Mengetahui,<br>
                                    Kepala Sekolah
                                    <div class="sig-space"></div>
                                    <strong><u>{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</u></strong><br>
                                    NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                                </td>
                            </tr>
                        </table>
                        @endif
                    </td>
                </tr>
            </tbody>

        </table>
    </div>
    @endforeach
</body>
</html>
