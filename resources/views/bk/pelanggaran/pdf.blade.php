<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Kasus Siswa</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .kop-surat img { max-width: 100%; max-height: 120px; }
        .kop-surat table { width: 100%; }
        .kop-surat td { vertical-align: middle; }
        .logo-td { width: 15%; text-align: left; }
        .text-td { width: 85%; text-align: center; }
        .nama-sekolah { font-size: 20px; font-weight: bold; margin: 0; }
        .alamat-sekolah { font-size: 12px; margin: 5px 0 0 0; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 11px; margin-bottom: 20px; color: #555; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .poin { font-weight: bold; color: #b91c1c; }
        .ttd { width: 100%; margin-top: 40px; }
        .ttd td { width: 50%; text-align: center; }
        .ttd-nama { font-weight: bold; text-decoration: underline; margin-top: 60px; }
    </style>
</head>
<body>

    <div class="kop-surat">
        <?php
            $base64 = null;
            if ($sekolah && $sekolah->kop_surat) {
                $kopSuratPath = public_path('uploads/identitas/' . $sekolah->kop_surat);
                if (file_exists($kopSuratPath)) {
                    $type = pathinfo($kopSuratPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($kopSuratPath);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        ?>
        @if($base64)
            <img src="{{ $base64 }}" alt="Kop Surat">
        @else
            <table>
                <tr>
                    <td class="logo-td">
                        @if($sekolah && $sekolah->logo)
                            <?php 
                                $logoPath = public_path('storage/' . $sekolah->logo);
                                if(file_exists($logoPath)) {
                                    $logoData = base64_encode(file_get_contents($logoPath));
                                    echo '<img src="data:image/png;base64,' . $logoData . '" alt="Logo">';
                                }
                            ?>
                        @endif
                    </td>
                    <td class="text-td">
                        <p class="nama-sekolah">{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
                        <p class="alamat-sekolah">{{ $sekolah->alamat ?? 'Alamat Sekolah' }} <br> Telp: {{ $sekolah->no_telp ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <div class="title">LAPORAN BUKU KASUS SISWA (KEDISIPLINAN)</div>
    <div class="subtitle">
        @if($filters['start_date'] && $filters['end_date'])
            Periode: {{ date('d/m/Y', strtotime($filters['start_date'])) }} s.d {{ date('d/m/Y', strtotime($filters['end_date'])) }}
        @else
            Seluruh Periode
        @endif
        <br>
        @if($filters['search'])
            (Pencarian: "{{ $filters['search'] }}")
        @endif
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal Kejadian</th>
                <th width="20%">Nama Siswa (Kelas)</th>
                <th width="25%">Kasus Pelanggaran</th>
                <th width="20%">Catatan / Kronologi</th>
                <th width="5%">Poin</th>
                <th width="10%">Pelapor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d/m/Y H:i', strtotime($row->tanggal)) }}</td>
                    <td>
                        <strong>{{ $row->siswa->nama_lengkap ?? '-' }}</strong><br>
                        NISN: {{ $row->siswa->nisn ?? '-' }} | Kelas: {{ $row->siswa->kelas->nama_kelas ?? '-' }}
                    </td>
                    <td>{{ $row->pelanggaran->nama_pelanggaran ?? '-' }}</td>
                    <td>{{ $row->catatan ?? '-' }}</td>
                    <td class="text-center poin">{{ $row->pelanggaran->poin ?? 0 }}</td>
                    <td class="text-center">{{ $row->pelapor->nama_lengkap ?? 'Sistem' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada riwayat pelanggaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <div class="ttd-nama">{{ $sekolah->nama_kepsek ?? '________________________' }}</div>
            </td>
            <td>
                {{ $sekolah->kota ?? 'Kota' }}, {{ date('d F Y') }}<br>
                Koordinator BK
                <div class="ttd-nama">________________________</div>
            </td>
        </tr>
    </table>

</body>
</html>
