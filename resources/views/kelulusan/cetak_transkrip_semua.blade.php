<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip - CETAK SEMUA</title>
    <style>
        /* CSS LAYOUT UMUM */
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.2; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 5px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; } 
        .judul-surat { text-align: center; margin-bottom: 15px; margin-top: 5px; }
        .judul-surat h3 { margin: 0; font-size: 13pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; }
        .judul-surat p { margin: 0; font-size: 10pt; }
        .biodata { width: 100%; margin-bottom: 10px; font-size: 10pt; line-height: 1.2; }
        .biodata td { padding: 2px 5px; vertical-align: top; }
        .biodata td:first-child { width: 160px; }
        .biodata td:nth-child(2) { width: 10px; }
        table.tabel-nilai { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5pt; }
        table.tabel-nilai th, table.tabel-nilai td { border: 1px solid #000; padding: 3px 4px; } 
        .thead-gray th { background-color: #d1d5db !important; text-align: center; font-weight: bold; vertical-align: middle; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .thead-blue th { background-color: #0056b3 !important; color: white !important; text-align: center; font-weight: bold; font-size: 8pt; padding: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .col-no { width: 4%; text-align: center; }
        .col-mapel { width: 36%; }
        .col-smt { width: 5%; text-align: center; }
        .col-rata { width: 10%; text-align: center; font-weight: bold;}
        .col-us { width: 10%; text-align: center; font-weight: bold;}
        .col-ket { width: 10%; text-align: center; }
        .kategori-row { font-weight: bold; background-color: #f1f1f1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        .no-print { background: #f8f9fa; padding: 15px; text-align: center; border-bottom: 1px solid #ccc; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; border-radius: 5px; font-weight: bold; border: none; background: #059669; color: white; cursor: pointer; font-size: 12pt; }
        
        @page { 
            size: 215mm 330mm portrait; /* F4 / Folio */
            margin: 10mm 15mm 10mm 15mm; 
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .cetak-container { display: flex; flex-direction: column; min-height: 98vh; }
            .main-content { flex: 1 1 auto; }
            .footer-arsip { flex: 0 0 auto; margin-top: auto; border-top: 1px solid #ddd; padding-top: 5px; }
            .hindari-terpotong { page-break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ CETAK DOKUMEN SEKARANG</button>
        <p style="margin-top:5px; font-size:10pt; color:#666;">Pilih Kertas <b>Legal / F4</b> di pengaturan printer Anda.</p>
    </div>

    @foreach($semuaData as $index => $data)
        @php
            $siswa = $data['siswa'];
            $sekolah = $data['sekolah'];
            $setting = $data['setting'];
            $nilai_group = $data['nilai_group'];
            $rata_rapor = $data['rata_rapor'];
            $rata_us = $data['rata_us'];
            $rata_prestasi = $data['rata_prestasi'];
            $nomor_surat = $data['nomor_surat'];
            $qr_url = $data['qr_url'];
            $token_validasi = $data['token_validasi'];
        @endphp

        <div class="cetak-container" style="{{ $index > 0 ? 'page-break-before: always; margin-top: 20px;' : '' }}">
            
            <div class="main-content">
                <div class="kop-surat">
                    @if(!empty($sekolah->kop_surat) && $sekolah->kop_surat !== 'default_kop.png')
                        <img src="{{ asset('uploads/identitas/' . $sekolah->kop_surat) }}" alt="Kop Surat">
                    @else
                        <h2 style="margin:0; font-family: Arial; font-size: 16pt;">{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h2>
                        <p style="margin:0; font-family: Arial; font-size: 10pt;">{{ $sekolah->alamat ?? '' }}, Kec. {{ $sekolah->kecamatan ?? '' }}, {{ $sekolah->kabupaten ?? '' }}</p>
                    @endif
                </div>

                <div class="judul-surat">
                    <h3>TRANSKRIP NILAI PESERTA DIDIK</h3>
                    <p>Nomor : {{ $nomor_surat }}</p>
                </div>

                <table class="biodata">
                    <tr><td>Nama</td><td>:</td><td style="font-weight: bold;">{{ strtoupper($siswa->nama_lengkap) }}</td></tr>
                    <tr><td>NIS / NISN</td><td>:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn }}</td></tr>
                    <tr><td>Tempat, Tgl. Lahir</td><td>:</td><td>{{ strtoupper($siswa->tempat_lahir ?? '-') }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->locale('id')->isoFormat('D MMMM Y') : '-' }}</td></tr>
                    <tr><td>Nomor Peserta Ujian</td><td>:</td><td>{{ $siswa->nisn }}</td></tr>
                    <tr><td>Kompetensi Keahlian</td><td>:</td><td>{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td></tr>
                </table>

                <table class="tabel-nilai">
                    <thead class="thead-gray">
                        <tr>
                            <th rowspan="2" class="col-no">NO</th><th rowspan="2" class="col-mapel">MATA PELAJARAN</th>
                            <th colspan="6">NILAI SEMESTER</th><th rowspan="2" class="col-rata">NILAI<br>RATA-RATA</th>
                            <th rowspan="2" class="col-us">NILAI US</th><th rowspan="2" class="col-ket">KET.</th>
                        </tr>
                        <tr>
                            <th class="col-smt">I</th><th class="col-smt">II</th><th class="col-smt">III</th>
                            <th class="col-smt">IV</th><th class="col-smt">V</th><th class="col-smt">VI</th>
                        </tr>
                    </thead>
                    <thead class="thead-blue"> 
                        <tr><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th></tr>
                    </thead>
                    <tbody>
                        @foreach($nilai_group as $kelompok => $mapels)
                            <tr><td colspan="11" class="kategori-row">{{ $kelompok }}</td></tr>
                            @php $nomor=1; @endphp
                            @foreach($mapels as $mp)
                            <tr>
                                <td class="col-no">{{ $nomor++ }}</td><td class="col-mapel">{{ $mp['nama_mapel'] }}</td>
                                <td class="col-smt">{{ $mp['s1'] > 0 ? $mp['s1'] : '-' }}</td><td class="col-smt">{{ $mp['s2'] > 0 ? $mp['s2'] : '-' }}</td>
                                <td class="col-smt">{{ $mp['s3'] > 0 ? $mp['s3'] : '-' }}</td><td class="col-smt">{{ $mp['s4'] > 0 ? $mp['s4'] : '-' }}</td>
                                <td class="col-smt">{{ $mp['s5'] > 0 ? $mp['s5'] : '-' }}</td><td class="col-smt">{{ $mp['s6'] > 0 ? $mp['s6'] : '-' }}</td>
                                <td class="col-rata">{{ $mp['rata_rapor'] > 0 ? number_format($mp['rata_rapor'], 2) : '-' }}</td>
                                <td class="col-us">{{ $mp['nilai_us'] > 0 ? number_format($mp['nilai_us'], 2) : '-' }}</td>
                                <td class="col-ket">{{ $mp['ket'] }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            <td colspan="8" style="text-align: center;">Rata-Rata</td>
                            <td class="col-rata">{{ number_format($rata_rapor, 2) }}</td>
                            <td class="col-us">{{ number_format($rata_us, 2) }}</td><td></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="hindari-terpotong" style="margin-top: 15px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                <table style="width: 100%; border: none; font-size: 10pt;">
                                    <tr><td style="width: 180px; font-weight: bold;">Nilai Prestasi Rata-rata</td><td style="width: 15px;">:</td><td style="font-weight: bold;">{{ number_format($rata_prestasi, 2) }}</td></tr>
                                    <tr><td style="font-weight: bold;">Kriteria Ketuntasan Minimal</td><td>:</td><td style="font-weight: bold;">70</td></tr>
                                </table>
                            </td>
                            <td style="width: 50%; padding-left: 20px; font-size: 10pt; vertical-align: top;">
                                {{ $sekolah->kabupaten ?? 'Subang' }}, {{ $setting->tgl_pengumuman ? \Carbon\Carbon::parse($setting->tgl_pengumuman)->locale('id')->isoFormat('D MMMM Y') : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                                
                                        @if(request()->has('ttd_manual') && request('ttd_manual') == 1)
                                        <table style="border: none; width: 240px; padding: 4px; margin-top: 6px;">
                                            <tr>
                                                <td style="font-family: Arial, sans-serif; font-size: 10pt; text-align: left; vertical-align: middle; padding-left: 5px;">
                                                    KEPALA SEKOLAH,<br><br><br><br><br>
                                                    <b>{{ $sekolah->nama_kepsek ?? '...........................................' }}</b><br>
                                                    NIP. {{ $sekolah->nip_kepsek ?? '.......................' }}
                                                </td>
                                            </tr>
                                        </table>
                                        @else
                                        <table style="border: 1px solid #000; border-radius: 8px; margin-top: 6px; background-color: #fff; width: 240px; padding: 4px;">
                                            <tr>
                                                <td style="width: 50px; text-align: center; vertical-align: middle;">
                                                    @php
                                                        $pathLogo = !empty($sekolah->logo) && file_exists(public_path('uploads/identitas/' . $sekolah->logo)) ? asset('uploads/identitas/' . $sekolah->logo) : asset('images/logo.png');
                                                    @endphp
                                                    <img src="{{ $pathLogo }}" alt="Logo Sekolah" style="width: 45px; height: 45px;">
                                                </td>
                                                <td style="font-family: Arial, sans-serif; font-size: 8pt; text-align: left; vertical-align: middle; padding-left: 5px;">
                                                    Ditandatangani secara elektronik oleh:<br>
                                                    KEPALA SEKOLAH,<br><br>
                                                    <b>{{ $sekolah->nama_kepsek ?? '...........................................' }}</b>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if(!(request()->has('ttd_manual') && request('ttd_manual') == 1))
            <div class="footer-arsip hindari-terpotong">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 80px; text-align: center; vertical-align: middle;">
                            <img src="{{ $qr_url }}" alt="QR Code" style="width: 55px; height: 55px; margin-bottom: 3px;"><br>
                            <span style="font-size: 7.5pt; font-weight: bold; color: #0056b3; font-family: monospace;">
                                {{ strtoupper(substr($token_validasi, 0, 8)) }}
                            </span>
                        </td>
                        <td style="vertical-align: middle; text-align: justify; padding-left: 10px; line-height: 1.3; font-size: 8pt; color: #222;">
                            Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh sistem <b>E-Arsip {{ strtoupper($sekolah->nama_sekolah ?? '') }}</b>. Dokumen digital yang asli dapat diverifikasi dengan memindai <i>QR Code</i> di samping, atau dengan mengakses tautan berikut ini:<br>
                            <a href="{{ route('surat.verifikasi', $token_validasi) }}" style="color: #2563eb; text-decoration: none; font-weight: bold;">
                                {{ route('surat.verifikasi', $token_validasi) }}
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            @endif

        </div>
    @endforeach
</body>
</html>
