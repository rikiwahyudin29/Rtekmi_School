<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SKL - CETAK SEMUA</title>
    <style>
        /* CSS LAYOUT UMUM */
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.25; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 10px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; } 
        .judul-surat { text-align: center; margin-bottom: 10px; }
        .judul-surat h3 { margin: 0; font-size: 13pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; }
        .judul-surat p { margin: 0; font-size: 11pt; }
        .biodata { width: 100%; margin-left: 20px; margin-bottom: 8px; font-size: 11pt; line-height: 1.3; }
        .biodata td { padding: 3px 5px; vertical-align: top; }
        .biodata td:first-child { width: 180px; }
        .biodata td:nth-child(2) { width: 10px; }
        .box-status-kelulusan { border: 2px solid #000; padding: 4px 30px; text-align: center; font-size: 13pt; font-weight: bold; display: table; margin: 6px auto; letter-spacing: 1px; }
        .status-coret { text-decoration: line-through; text-decoration-thickness: 2px; }
        .status-kelulusan { text-align: justify; margin-bottom: 8px; font-size: 11pt; }
        
        table.tabel-nilai { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10pt; }
        table.tabel-nilai th, table.tabel-nilai td { border: 1px solid #000; padding: 4px 6px; }
        table.tabel-nilai th { text-align: center; font-weight: bold; }
        .col-no { width: 5%; text-align: center; }
        .col-mapel { width: 75%; }
        .col-nilai { width: 20%; text-align: center; }
        .kategori-row { font-weight: bold; background-color: #f1f1f1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .penutup-surat { text-align: justify; margin-bottom: 12px; font-size: 11pt; }
        
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
            $rata_rata = $data['rata_rata'];
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
                    <h3>SURAT KETERANGAN LULUS</h3>
                    <p>Nomor : {{ $nomor_surat }}</p>
                </div>

                <div class="penutup-surat">
                    {!! $setting->pembuka_surat ?? '' !!}
                </div>

                <table class="biodata">
                    <tr><td>Nama</td><td>:</td><td style="font-weight: bold;">{{ strtoupper($siswa->nama_lengkap) }}</td></tr>
                    <tr><td>Tempat, Tgl Lahir</td><td>:</td><td>{{ strtoupper($siswa->tempat_lahir ?? '-') }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->locale('id')->isoFormat('D MMMM Y') : '-' }}</td></tr>
                    <tr><td>Nama Ortu/Wali</td><td>:</td><td>{{ $siswa->nama_ayah ?? ($siswa->nama_ibu ?? '-') }}</td></tr>
                    <tr><td>NIS / NISN</td><td>:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn }}</td></tr>
                    <tr><td>Peminatan/Jurusan</td><td>:</td><td>{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td></tr>
                </table>

                <div class="status-kelulusan">Yang bersangkutan dinyatakan:</div>
                <div class="box-status-kelulusan">
                    @if(strtoupper($siswa->kelulusan->status_lulus ?? '') == 'LULUS')
                        LULUS / <span class="status-coret">TIDAK LULUS</span>
                    @else
                        <span class="status-coret">LULUS</span> / TIDAK LULUS
                    @endif
                </div>
                <div class="status-kelulusan">dengan perolehan nilai sebagai berikut.</div>

                <table class="tabel-nilai">
                    <thead>
                        <tr><th class="col-no">No.</th><th class="col-mapel">Mata Pelajaran</th><th class="col-nilai">Nilai SKL</th></tr>
                    </thead>
                    <tbody>
                        @foreach($nilai_group as $kelompok => $mapels)
                            <tr><td colspan="3" class="kategori-row">{{ $kelompok }}</td></tr>
                            @php $nomor=1; @endphp
                            @foreach($mapels as $mp)
                            <tr><td class="col-no">{{ $nomor++ }}.</td><td class="col-mapel">{{ $mp['nama_mapel'] }}</td><td class="col-nilai">{{ number_format($mp['nilai_akhir'], 2) }}</td></tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="2" style="text-align: center;">Rata-rata</th><th>{{ number_format($rata_rata, 2) }}</th></tr>
                    </tfoot>
                </table>

                <div class="penutup-surat">
                    {!! $setting->penutup_surat ?? '' !!}
                </div>

                <div class="hindari-terpotong" style="margin-top: 10px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%;"></td>
                            <td style="width: 50%; padding-left: 20px;">
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
