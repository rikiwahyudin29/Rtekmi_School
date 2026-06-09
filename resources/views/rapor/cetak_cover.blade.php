<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Cover Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; text-align: center; margin: 0; padding: 20px; }
        .page { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 2cm; box-sizing: border-box; border: 1px solid #ccc; background: white; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; }
        }
        .garuda { width: 120px; height: auto; margin-top: 50px; }
        .title { font-size: 24px; font-weight: bold; margin-top: 40px; line-height: 1.5; text-transform: uppercase; }
        .subtitle { font-size: 18px; margin-top: 50px; font-weight: bold; }
        .student-name { font-size: 22px; font-weight: bold; margin-top: 30px; text-decoration: underline; text-transform: uppercase; }
        .student-nis { font-size: 18px; margin-top: 10px; }
        .footer-kemdikbud { margin-top: auto; padding-top: 150px; font-size: 20px; font-weight: bold; text-transform: uppercase; line-height: 1.5; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <!-- Optional Garuda PNG if available -->
        <!-- <img src="/images/garuda.png" alt="Garuda" class="garuda"> -->
        <div style="font-size: 60px; margin-top: 50px;">🦅</div>
        
        <div class="title">
            RAPOR PESERTA DIDIK<br>
            SEKOLAH MENENGAH KEJURUAN<br>
            (SMK)
        </div>

        <div class="subtitle">Nama Peserta Didik:</div>
        <div class="student-name">{{ $siswa->nama_lengkap }}</div>
        <div class="student-nis">NISN / NIS: {{ $siswa->nisn }} / {{ $siswa->nis ?? '-' }}</div>

        <div class="footer-kemdikbud">
            KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,<br>
            RISET, DAN TEKNOLOGI<br>
            REPUBLIK INDONESIA
        </div>
    </div>
</body>
</html>
