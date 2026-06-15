<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Cover Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; text-align: center; margin: 0; padding: 0; background: #fff; }
        .page { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 2cm; padding-top: 4cm; box-sizing: border-box; background: white; display: flex; flex-direction: column; align-items: center; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; height: 100vh; padding-top: 4cm; }
        }
        .logo-container { margin-bottom: 30px; }
        .logo { width: 150px; height: auto; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 80px; text-transform: uppercase; }
        
        .box-label { font-size: 18px; font-weight: bold; margin-bottom: 10px; margin-top: 30px; }
        .box-value { 
            border: 1px solid #000; 
            padding: 15px 30px; 
            font-size: 20px; 
            font-weight: bold; 
            width: 400px; 
            text-transform: uppercase; 
            margin-bottom: 20px;
        }

        .footer-kemdikbud { margin-top: auto; padding-bottom: 2cm; font-size: 20px; font-weight: bold; text-transform: uppercase; line-height: 1.5; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <!-- Logo - using garuda/tut wuri handayani in real app, fallback to generic or text -->
        <div class="logo-container">
            <img src="/assets/images/tutwuri.png" alt="Logo" class="logo" onerror="this.style.display='none'">
        </div>
        
        <div class="title">
            SEKOLAH MENENGAH KEJURUAN<br>
            ( SMK )
        </div>

        <div class="box-label">Nama Murid</div>
        <div class="box-value">{{ $siswa->nama_lengkap }}</div>

        <div class="box-label">NISN / NIS</div>
        <div class="box-value">{{ $siswa->nisn }} / {{ $siswa->nis ?? '-' }}</div>

        <div class="footer-kemdikbud">
            KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH<br>
            REPUBLIK INDONESIA
        </div>
    </div>
</body>
</html>
