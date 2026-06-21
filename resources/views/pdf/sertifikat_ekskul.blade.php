<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Ekstrakurikuler</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333333;
        }
        .container {
            width: 100%;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            position: relative;
        }
        .border-outer {
            border: 10px solid #1e3a8a; /* Tailwind blue-900 */
            padding: 5px;
            height: 90%;
            position: relative;
        }
        .border-inner {
            border: 2px solid #3b82f6; /* Tailwind blue-500 */
            height: 98%;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .header {
            margin-bottom: 20px;
        }
        .title {
            font-size: 36px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 2px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 30px;
        }
        .content {
            margin-top: 20px;
            font-size: 18px;
            line-height: 1.6;
        }
        .name {
            font-size: 30px;
            font-weight: bold;
            color: #0f172a;
            margin: 20px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 5px;
            min-width: 400px;
        }
        .details {
            font-size: 16px;
            margin-bottom: 30px;
        }
        .achievement {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 20px 0;
        }
        .description {
            font-size: 14px;
            font-style: italic;
            color: #475569;
            max-width: 80%;
            margin: 0 auto;
            margin-bottom: 40px;
        }
        .footer {
            width: 100%;
            position: absolute;
            bottom: 40px;
            left: 0;
        }
        .signature-table {
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }
        .signature-cell {
            width: 50%;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin: 60px auto 5px auto;
        }
        .token {
            position: absolute;
            bottom: 10px;
            left: 20px;
            font-size: 10px;
            color: #94a3b8;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="border-outer">
            <div class="border-inner">
                
                <div class="header">
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</div>
                    <div style="font-size: 12px; color: #64748b;">{{ $sekolah->alamat ?? 'Alamat Sekolah' }}</div>
                </div>

                <div class="title">Sertifikat Penghargaan</div>
                <div class="subtitle">Nomor: {{ $nilai->token_sertifikat }}</div>

                <div class="content">
                    Diberikan dengan bangga kepada:
                    
                    <div class="name">{{ $nilai->nama_lengkap }}</div>
                    
                    <div class="details">
                        NIS/NISN: {{ $nilai->nis }} / {{ $nilai->nisn }}<br>
                        Kelas: {{ $nilai->nama_kelas }}
                    </div>

                    Telah berpartisipasi aktif dan menyelesaikan program kegiatan ekstrakurikuler:
                    
                    <div class="achievement">"{{ $nilai->nama_ekskul }}"</div>

                    <div class="description">
                        "{{ $nilai->deskripsi_dapodik }}"<br>
                        Dengan Nilai Akhir: <b>{{ $nilai->nilai_huruf }}</b> (Kehadiran: {{ $nilai->persen_hadir }}%)
                    </div>
                </div>

                <div class="footer">
                    <table class="signature-table">
                        <tr>
                            <td class="signature-cell">
                                <div>Kepala Sekolah</div>
                                <div class="signature-line"></div>
                                <div><b>{{ $kepsek->nama_lengkap ?? '_______________________' }}</b></div>
                                <div style="font-size: 12px;">NIP. {{ $kepsek->nip ?? '-' }}</div>
                            </td>
                            <td class="signature-cell">
                                <div>Diberikan pada tanggal, {{ $tanggal_cetak }}</div>
                                <div>Pembina Ekstrakurikuler</div>
                                <div class="signature-line"></div>
                                <div><b>{{ $pembina->nama_lengkap ?? '_______________________' }}</b></div>
                                <div style="font-size: 12px;">NIP. {{ $pembina->nip ?? '-' }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="token">Validasi Token: {{ $nilai->token_sertifikat }} | Digenerate oleh Sistem SIAKAD</div>
            </div>
        </div>
    </div>
</body>
</html>
