<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat PKL</title>
    <style>
        /* ================= SETUP KERTAS ================= */
        @page { margin: 0px; size: A4 landscape; }
        body { margin: 0; padding: 0; font-family: "Times New Roman", Times, serif; color: #000; line-height: 1.1; }
        
        /* ================= HALAMAN 1 (FIXED ABSOLUTE) ================= */
        .border-gold { position: absolute; top: 30px; bottom: 30px; left: 30px; right: 30px; border: 6px double #d4af37; z-index: -2; }
        .border-blue { position: absolute; top: 40px; bottom: 40px; left: 40px; right: 40px; border: 2px solid #1e3a8a; z-index: -1; }
        .watermark { position: absolute; top: 15%; left: 30%; width: 40%; opacity: 0.04; z-index: -1; }
        
        .content-top { position: absolute; top: 50px; left: 60px; right: 60px; text-align: center; }
        .content-bottom { position: absolute; bottom: 50px; left: 60px; right: 60px; }

        table.kop { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 15px; text-align: left; }
        .cert-title { font-family: "Georgia", serif; font-size: 38pt; font-weight: bold; color: #1e3a8a; letter-spacing: 5px; margin: 10px 0 5px 0; }
        .cert-subtitle { font-size: 14pt; font-weight: bold; letter-spacing: 2px; text-decoration: underline; margin-bottom: 20px; }
        .student-name { font-size: 28pt; font-weight: bold; color: #1e3a8a; text-decoration: underline; margin: 10px 0; text-transform: uppercase; }
        .desc { font-size: 13pt; line-height: 1.5; padding: 0 40px; }

        table.ttd-box { width: 100%; text-align: center; font-size: 11pt; }
        table.ttd-box td { width: 50%; vertical-align: bottom; }

        /* ================= HALAMAN 2 (TRANSKRIP AUTO-SCALE) ================= */
        .page-2 { page-break-before: always; padding: 25px 40px; position: relative; } /* Padding dikurangi drastis */
        
        table.info-siswa { width: 100%; font-size: {{ $fs_info }}; margin-bottom: 8px; text-align: left; }
        table.info-siswa td { padding: 1px 0; }

        table.transkrip { width: 100%; border-collapse: collapse; font-size: {{ $fs_tabel }}; margin-bottom: {{ $mb_tabel }}; }
        table.transkrip th, table.transkrip td { border: 1px solid #000; padding: {{ $pd_tabel }}; }
        table.transkrip th { background-color: #f3f4f6; text-align: center; font-weight: bold; }
        
        .bg-gray { background-color: #f3f4f6; font-weight: bold; }
        .bg-blue { background-color: #eff6ff; font-weight: bold; }
        .bg-green { background-color: #dcfce7; font-weight: bold; }
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
    if(!function_exists('getHurufPkl')) {
        function getHurufPkl($nilai) {
            if($nilai >= 90) return 'A';
            if($nilai >= 80) return 'B';
            if($nilai >= 70) return 'C';
            return 'D';
        }
    }
    @endphp

    <div class="border-gold"></div>
    <div class="border-blue"></div>
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark">
    @endif

    <div class="content-top">
        <table class="kop">
            <tr>
                <td width="12%">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 75px;">
                    @endif
                </td>
                <td width="58%">
                    <div style="font-size: 17pt; font-weight: bold; text-transform: uppercase;">{{ $sekolah->nama_sekolah ?? '-' }}</div>
                    <div style="font-size: 10pt; font-style: italic;">{{ $sekolah->alamat ?? '-' }}, Kab. {{ $sekolah->kabupaten ?? '-' }} - {{ $sekolah->provinsi ?? '-' }}</div>
                </td>
                <td width="30%" style="text-align: right; font-weight: bold; font-size: 11pt; vertical-align: top; padding-top: 10px;">
                    No. Sertifikat: {{ $d->no_sertifikat ?? '-' }}
                </td>
            </tr>
        </table>

        <div class="cert-title">SERTIFIKAT</div>
        <div class="cert-subtitle">PRAKTIK KERJA LAPANGAN</div>

        <div style="font-size: 13pt;">Diberikan kepada:</div>
        <div class="student-name">{{ $d->nama_siswa ?? '-' }}</div>
        <div style="font-size: 13pt; font-weight: bold; margin-bottom: 25px;">NIS/NISN : {{ $d->nis ?? '-' }} / {{ $d->nisn ?? '-' }}</div>

        <div class="desc">
            Telah dinyatakan <b>LULUS</b> dalam melaksanakan Praktik Kerja Lapangan (PKL) di <br>
            <b>"{{ strtoupper($d->nama_dudi ?? '-') }}"</b><br> 
            dengan Predikat <b>"{{ $d->predikat ?? '-' }}"</b> (Skor Akhir: {{ $d->nilai_akhir ?? 0 }}).
        </div>
    </div>

    <div class="content-bottom">
        <table class="ttd-box">
            <tr>
                <td>
                    <p style="margin-bottom: 75px;">Pembimbing Industri,</p>
                    <p style="font-weight: bold; text-decoration: underline; margin:0; text-transform: uppercase;">................................................</p>
                    <p style="font-size: 10pt; margin-top:2px;">NIP/NIK.</p>
                </td>
                <td>
                    <p style="margin-bottom: 5px;">Kab. {{ $sekolah->kabupaten ?? '-' }}, {{ \Carbon\Carbon::parse($d->tgl_selesai ?? now())->isoFormat('D MMMM Y') }}</p>
                    <p style="margin-bottom: 2px;">Kepala Sekolah,</p>
                    
                    @if($qrBase64)
                        <div><img src="{{ $qrBase64 }}" style="height: 75px; margin: 3px 0;"></div>
                    @endif
                    
                    <p style="font-weight: bold; text-decoration: underline; margin:0; text-transform: uppercase;">{{ $sekolah->nama_kepsek ?? '-' }}</p>
                    <p style="font-size: 10pt; margin:2px 0;">NIP. {{ $sekolah->nip_kepsek ?? '-' }}</p>
                    <p style="font-size: 7pt; color: #666; margin-top:2px;">Dokumen ditandatangani secara elektronik</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-2">
        <div class="text-center" style="margin-bottom: 8px;">
            <h2 style="margin: 0; font-size: 11.5pt;">PENILAIAN PRAKTIK KERJA LAPANGAN (PKL)</h2>
            <h3 style="margin: 2px 0; font-size: 10pt;">DI DUNIA USAHA / INDUSTRI</h3>
            <p style="margin: 0; font-size: 9pt; font-weight: bold;">TAHUN PELAJARAN {{ date('Y') . '/' . (date('Y')+1) }}</p>
        </div>

        <table class="info-siswa">
            <tr><td width="20%">Nama Peserta Didik</td><td width="2%">:</td><td class="font-bold">{{ strtoupper($d->nama_siswa ?? '-') }}</td></tr>
            <tr><td>NIS / NISN</td><td>:</td><td>{{ $d->nis ?? '-' }} / {{ $d->nisn ?? '-' }}</td></tr>
            <tr><td>Nama Industri</td><td>:</td><td class="font-bold">{{ strtoupper($d->nama_dudi ?? '-') }}</td></tr>
        </table>

        <table class="transkrip">
            <thead>
                <tr>
                    <th rowspan="2" width="5%">No.</th>
                    <th rowspan="2" width="60%">KOMPONEN PENILAIAN</th>
                    <th colspan="2" width="20%">SKOR (0-100)</th>
                    <th rowspan="2" width="15%">Ket.</th>
                </tr>
                <tr>
                    <th width="10%">ANGKA</th>
                    <th width="10%">HURUF</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="text-center font-bold">1.</td><td colspan="4" class="bg-gray">Aspek Sikap</td></tr>
                @php $char = 'a'; @endphp
                @foreach($sikap as $n)
                    <tr>
                        <td class="text-center">{{ $char++ }}.</td>
                        <td>{{ $n->aspek ?? '-' }}</td>
                        <td class="text-center">{{ $n->skor ?? 0 }}</td>
                        <td class="text-center">{{ getHurufPkl($n->skor ?? 0) }}</td>
                        <td></td>
                    </tr>
                @endforeach

                <tr><td class="text-center font-bold">2.</td><td colspan="4" class="bg-gray">Aspek Pengetahuan</td></tr>
                @php $char = 'a'; @endphp
                @foreach($pengetahuan as $n)
                    <tr>
                        <td class="text-center">{{ $char++ }}.</td>
                        <td>{{ $n->aspek ?? '-' }}</td>
                        <td class="text-center">{{ $n->skor ?? 0 }}</td>
                        <td class="text-center">{{ getHurufPkl($n->skor ?? 0) }}</td>
                        <td></td>
                    </tr>
                @endforeach

                <tr><td class="text-center font-bold">3.</td><td colspan="4" class="bg-gray">Aspek Keterampilan</td></tr>
                @php $char = 'a'; @endphp
                @foreach($keterampilan as $n)
                    <tr>
                        <td class="text-center">{{ $char++ }}.</td>
                        <td>{{ $n->aspek ?? '-' }}</td>
                        <td class="text-center">{{ $n->skor ?? 0 }}</td>
                        <td class="text-center">{{ getHurufPkl($n->skor ?? 0) }}</td>
                        <td></td>
                    </tr>
                @endforeach

                @php 
                    $avgAspek = ($d->nilai_sikap_rata + $d->nilai_pengetahuan_rata + $d->nilai_keterampilan_rata) / 3;
                @endphp
                <tr class="bg-blue">
                    <td colspan="2" style="text-align: right; padding-right: 15px;">Nilai Rata-rata Aspek 1, 2, & 3 (Bobot 80%)</td>
                    <td class="text-center">{{ round($avgAspek, 2) }}</td>
                    <td class="text-center">{{ getHurufPkl($avgAspek) }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-center font-bold">4.</td>
                    <td class="font-bold">Nilai Laporan PKL (Bobot 20%)</td>
                    <td class="text-center font-bold">{{ $d->nilai_laporan ?? 0 }}</td>
                    <td class="text-center font-bold">{{ getHurufPkl($d->nilai_laporan ?? 0) }}</td>
                    <td></td>
                </tr>
                <tr class="bg-green">
                    <td colspan="2" style="text-align: right; padding-right: 15px; text-transform: uppercase;">Nilai Akhir PKL</td>
                    <td class="text-center font-bold">{{ $d->nilai_akhir ?? 0 }}</td>
                    <td class="text-center font-bold">{{ getHurufPkl($d->nilai_akhir ?? 0) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <table style="width: 100%; font-size: {{ $fs_ttd }};">
            <tr>
                <td width="40%" style="vertical-align: top;">
                    <p class="font-bold" style="margin: 0 0 3px 0;">Keterangan Predikat:</p>
                    <table style="width: 100%; font-size: {{ $fs_ttd }};">
                        <tr><td width="25%" style="padding:1px 0;">90 - 100</td><td width="5%" style="padding:1px 0;">=</td><td style="padding:1px 0;">A (Istimewa)</td></tr>
                        <tr><td style="padding:1px 0;">80 - 89</td><td style="padding:1px 0;">=</td><td style="padding:1px 0;">B (Baik)</td></tr>
                        <tr><td style="padding:1px 0;">70 - 79</td><td style="padding:1px 0;">=</td><td style="padding:1px 0;">C (Cukup)</td></tr>
                        <tr><td style="padding:1px 0;">< 70</td><td style="padding:1px 0;">=</td><td style="padding:1px 0;">D (Kurang)</td></tr>
                    </table>
                </td>
                <td width="60%" style="text-align: center; vertical-align: top;">
                    <p style="margin: 0 0 2px 0;">Kab. {{ $sekolah->kabupaten ?? '-' }}, {{ \Carbon\Carbon::parse($d->tgl_selesai ?? now())->isoFormat('D MMMM Y') }}</p>
                    <p style="margin: 0;">Pembimbing Industri,</p>
                    <div style="height: {{ $gap_ttd }};"></div>
                    <p style="font-weight: bold; text-decoration: underline; text-transform: uppercase; margin:0;">................................................</p>
                    <p style="margin-top:2px;">(Nama & Stempel Instansi)</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
