<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Cover Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        @page {
            size: {{ request('kertas', 'A4') }};
            margin: {{ request('margin_atas', 20) }}mm {{ request('margin_kanan', 20) }}mm {{ request('margin_bawah', 20) }}mm {{ request('margin_kiri', 20) }}mm;
        }
        body { font-family: 'Times New Roman', Times, serif; margin: 0; padding: 0; background: #fff; }
        .page { width: 100%; box-sizing: border-box; background: white; text-align: center; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; height: 100vh; page-break-after: always; }
        }
        
        .logo { width: 160px; margin: 0 auto; }
        .title-2 { font-size: 26px; font-weight: bold; margin-bottom: 10px; }
        .title-3 { font-size: 20px; font-weight: bold; margin-bottom: 10px; margin-top: 20px; }
        
        .student-box { border: 2px solid #000; width: 60%; margin: 0 auto; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 30px; text-transform: uppercase; }
        
        .footer-text { font-size: 20px; font-weight: bold; line-height: 1.5; margin-top: 60px; text-transform: uppercase; }
    </style>
</head>
<body onload="window.print()">
    @php
        $pathLogo = !empty($sekolah->logo) && file_exists(public_path('uploads/identitas/' . $sekolah->logo)) ? asset('uploads/identitas/' . $sekolah->logo) : asset('assets/img/logo.png');
    @endphp

    <div class="page" style="padding: 1cm 2cm 2cm 2cm;">
        <div style="padding-top: 1cm;">
            
            <div style="margin-bottom: 20px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/9c/Logo_of_Ministry_of_Education_and_Culture_of_Republic_of_Indonesia.svg" alt="Logo Kemendikdasmen" class="logo" style="width: 140px;">
            </div>

            <div class="title-2">SEKOLAH MENENGAH KEJURUAN<br>( SMK )</div>
            
            <div style="margin: 40px 0;">
                <img src="{{ $pathLogo }}" alt="Logo" class="logo">
            </div>

            <div class="title-3">Nama Murid</div>
            <div class="student-box">
                {{ $siswa->nama_lengkap }}
            </div>
            
            <div class="title-3">NISN / NIS</div>
            <div class="student-box">
                {{ $siswa->nisn }} / {{ $siswa->nis ?? '-' }}
            </div>

            <div class="footer-text">
                KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH<br>
                REPUBLIK INDONESIA
            </div>
        </div>
    </div>
</body>
</html>
