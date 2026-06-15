<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Cover Masal</title>
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
        
        .logo { width: 150px; margin: 40px auto; }
        .title-1 { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .title-2 { font-size: 28px; font-weight: bold; margin-bottom: 50px; }
        .title-3 { font-size: 20px; font-weight: bold; margin-bottom: 80px; }
        
        .student-box { border: 2px solid #000; width: 60%; margin: 0 auto; padding: 30px; text-align: left; font-size: 16px; margin-bottom: 100px; }
        .student-table { width: 100%; }
        .student-table td { padding: 8px 0; }
        .st-label { width: 40%; font-weight: bold; }
        .st-colon { width: 5%; }
        .st-value { width: 55%; border-bottom: 1px dotted #000; }
        
        .footer-text { font-size: 18px; font-weight: bold; line-height: 1.5; }
    </style>
</head>
<body onload="window.print()">
    @foreach($siswas as $siswa)
    <div class="page" style="padding: 2cm;">
        <div style="padding-top: 2cm;">
            <div class="title-1">RAPOR</div>
            <div class="title-2">SEKOLAH MENENGAH KEJURUAN<br>( SMK )</div>
            
            <div>
                @if($sekolah->logo)
                    <img src="{{ Storage::url('sekolah/'.$sekolah->logo) }}" alt="Logo" class="logo">
                @else
                    <img src="/assets/images/tutwuri.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                @endif
            </div>

            <div class="title-3">Nama Peserta Didik</div>

            <div class="student-box">
                <table class="student-table">
                    <tr>
                        <td class="st-label">Nama Murid</td>
                        <td class="st-colon">:</td>
                        <td class="st-value">{{ $siswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td class="st-label">NISN</td>
                        <td class="st-colon">:</td>
                        <td class="st-value">{{ $siswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td class="st-label">NIS</td>
                        <td class="st-colon">:</td>
                        <td class="st-value">{{ $siswa->nis ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="footer-text">
                KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,<br>
                RISET, DAN TEKNOLOGI<br>
                REPUBLIK INDONESIA
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
