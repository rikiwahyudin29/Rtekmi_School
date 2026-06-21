<!DOCTYPE html>
<html>
<head>
    <title>Data Inventaris Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header img { max-width: 100%; max-height: 120px; }
        .header-title { text-align: center; margin-bottom: 15px; }
        .header-title h2 { margin: 0; padding: 0; font-size: 16px; text-transform: uppercase; }
        .header-title p { margin: 5px 0 0 0; font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        td { font-size: 11px; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 30px; font-size: 10px; color: #777; text-align: right; border-top: 1px solid #ddd; padding-top: 5px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
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
            <h2>{{ $sekolah->nama_sekolah ?? 'INSTITUSI PENDIDIKAN' }}</h2>
            <p>{{ $sekolah->alamat ?? '' }}</p>
        @endif
    </div>

    <div class="header-title">
        <h2>Laporan Data Inventaris Aset & Fasilitas</h2>
        <p>Dicetak pada: {{ date('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Kode Barang</th>
                <th width="20%">Nama Aset</th>
                <th width="15%">Kategori</th>
                <th width="15%">Lokasi / Ruangan</th>
                <th width="10%" class="text-center">Jumlah</th>
                <th width="10%" class="text-center">Kondisi</th>
                <th width="10%" class="text-center">Tgl Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td><b>{{ $item->nama_barang }}</b></td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->lokasi }}</td>
                    <td class="text-center">{{ $item->jumlah }}</td>
                    <td class="text-center">{{ $item->kondisi }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($item->tgl_masuk)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data inventaris ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Halaman <span class="pagenum"></span> - Sistem Informasi Akademik
    </div>
</body>
</html>
