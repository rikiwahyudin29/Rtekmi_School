<?php
$pathKop = 'uploads/identitas/' . ($sekolah->kop_surat ?? 'default.png');
$pakaiKopGambar = !empty($sekolah->kop_surat) && file_exists(public_path($pathKop));
$pathLogo = !empty($sekolah->logo) && file_exists(public_path('uploads/identitas/' . $sekolah->logo)) ? asset('uploads/identitas/' . $sekolah->logo) : asset('assets/img/logo.png'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat - {{ $surat->no_surat }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.25; margin: 0; padding: 20px; }
        .kop-surat { width: 100%; margin-bottom: 10px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; } 
        .kop-surat-inner { border-bottom: 3px solid #000; padding-bottom: 10px; display: flex; width: 100%; align-items: center; border-bottom: 3px solid #000; margin-bottom: 2px;}
        .kop-logo { width: 90px; height: 90px; object-fit: contain; margin-right: 20px; }
        .kop-text { text-align: center; flex: 1; }
        .kop-text h1, .kop-text h2, .kop-text h3 { margin: 0; font-weight: bold; }
        .kop-text p { margin: 0; font-size: 11pt; }
        
        .isi-surat { margin-top: 20px; text-align: justify; line-height: 1.5; font-size: 11pt; }
        
        .hindari-terpotong { page-break-inside: avoid; }
        
        .no-print { background: #f8f9fa; padding: 15px; text-align: center; border-bottom: 1px solid #ccc; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; border-radius: 5px; font-weight: bold; border: none; background: #059669; color: white; cursor: pointer; font-size: 12pt; }

        @page { 
            size: 215mm 330mm portrait; /* F4 / Folio */
            margin: 10mm 15mm 10mm 15mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .cetak-container {
                display: flex;
                flex-direction: column;
                min-height: 98vh;
            }
            .main-content {
                flex: 1 1 auto;
            }
            .footer-arsip {
                flex: 0 0 auto;
                margin-top: auto;
                border-top: 1px solid #ddd;
                padding-top: 5px;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ CETAK DOKUMEN SEKARANG</button>
        <p style="margin-top:5px; font-size:10pt; color:#666;">Pilih Kertas <b>Legal / F4</b> di pengaturan printer Anda.</p>
    </div>

    <div class="cetak-container">
        <div class="main-content">
            <!-- Kop Surat -->
            <div class="kop-surat">
                @if($pakaiKopGambar)
                    <img src="{{ asset($pathKop) }}" alt="Kop Surat">
                @else
                    <div class="kop-surat-inner">
                        <img src="{{ $pathLogo }}" alt="Logo Sekolah" class="kop-logo">
                        <div class="kop-text">
                            <h3>PEMERINTAH PROVINSI DAERAH</h3>
                            <h2>DINAS PENDIDIKAN</h2>
                            <h1>{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
                            <p>{{ $sekolah->alamat ?? 'Alamat Sekolah' }}</p>
                            <p>Website: {{ $sekolah->website ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Isi Surat -->
            <div class="isi-surat">
                {!! $surat->isi_final !!}
            </div>

            <!-- TANDA TANGAN (Ikut di konten utama) -->
            <div class="hindari-terpotong" style="margin-top: 30px;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%; padding-left: 20px;">
                            {{ $sekolah->kabupaten ?? 'Subang' }}, {{ \Carbon\Carbon::parse($surat->tgl_surat)->locale('id')->isoFormat('D MMMM Y') }}<br>
                            
                            <table style="border: 1px solid #000; border-radius: 8px; margin-top: 6px; background-color: #fff; width: 240px; padding: 4px;">
                                <tr>
                                    <td style="width: 50px; text-align: center; vertical-align: middle;">
                                        <img src="{{ $pathLogo }}" alt="Logo Sekolah" style="width: 45px; height: 45px;">
                                    </td>
                                    <td style="font-family: Arial, sans-serif; font-size: 8pt; text-align: left; vertical-align: middle; padding-left: 5px;">
                                        Ditandatangani secara elektronik oleh:<br>
                                        KEPALA SEKOLAH,<br><br>
                                        <b>{{ $sekolah->nama_kepsek ?? '...........................................' }}</b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- BAGIAN BAWAH: FOOTER E-ARSIP -->
        <div class="footer-arsip hindari-terpotong">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 80px; text-align: center; vertical-align: middle;">
                        <img src="{{ $qr_link }}" alt="QR Code" style="width: 55px; height: 55px; margin-bottom: 3px;"><br>
                        <span style="font-size: 7.5pt; font-weight: bold; color: #0056b3; font-family: monospace;">
                            {{ strtoupper(substr($surat->token_validasi, 0, 8)) }}
                        </span>
                    </td>
                    <td style="vertical-align: middle; text-align: justify; padding-left: 10px; line-height: 1.3; font-size: 8pt; color: #222;">
                        Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh sistem <b>E-Arsip {{ strtoupper($sekolah->nama_sekolah ?? 'SEKOLAH') }}</b>. Dokumen digital yang asli dapat diverifikasi dengan memindai <i>QR Code</i> di samping, atau dengan mengakses tautan berikut ini:<br>
                        <a href="{{ url('/verifikasi/' . $surat->token_validasi) }}" style="color: #2563eb; text-decoration: none; font-weight: bold;">
                            {{ url('/verifikasi/' . $surat->token_validasi) }}
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
