<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Kompetensi - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 14pt; text-align: center; margin: 40px; border: 10px solid #ddd; padding: 40px; }
        .header { font-size: 24pt; font-weight: bold; margin-bottom: 10px; }
        .sub-header { font-size: 16pt; margin-bottom: 40px; }
        .content { margin-bottom: 40px; line-height: 1.5; }
        .footer { display: flex; justify-content: space-between; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">SERTIFIKAT KOMPETENSI</div>
    <div class="sub-header">UJI KOMPETENSI KEAHLIAN (UKK)</div>
    
    <div class="content">
        Diberikan kepada:<br>
        <b style="font-size: 20pt;">{{ $siswa->nama_lengkap }}</b><br>
        NISN: {{ $siswa->nisn }}<br><br>
        Telah mengikuti dan dinyatakan:<br>
        <b style="font-size: 18pt;">{{ $ukk->kesimpulan ?? 'Belum Mengikuti UKK' }}</b><br><br>
        Pada Paket Kompetensi:<br>
        <b>{{ $ukk->paket->nama_paket ?? '-' }}</b><br>
        Kompetensi Keahlian: {{ $siswa->jurusan->nama_jurusan ?? '-' }}
    </div>

    <div class="footer">
        <div>
            Asesor Internal<br><br><br><br>
            <b>{{ $ukk->asesorInternal->nama_lengkap ?? '.........................' }}</b>
        </div>
        <div>
            Asesor Eksternal (DUDI)<br><br><br><br>
            <b>{{ $ukk->asesorEksternal->nama_asesor ?? '.........................' }}</b>
        </div>
    </div>

    <script>window.print();</script>
</body>
</html>
