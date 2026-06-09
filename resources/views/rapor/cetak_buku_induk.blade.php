<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Induk - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11pt; }
        .header { text-align: center; font-weight: bold; font-size: 16pt; margin-bottom: 20px; text-decoration: underline; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 5px; }
        .no-border td { border: none; padding: 2px 5px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">BUKU INDUK PESERTA DIDIK</div>
    
    <table class="no-border" style="width: 50%;">
        <tr><td>Nama Lengkap</td><td>:</td><td>{{ $siswa->nama_lengkap }}</td></tr>
        <tr><td>NIS/NISN</td><td>:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn }}</td></tr>
        <tr><td>Tempat, Tgl Lahir</td><td>:</td><td>{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir }}</td></tr>
        <tr><td>Agama</td><td>:</td><td>{{ $siswa->agama }}</td></tr>
        <tr><td>Alamat</td><td>:</td><td>{{ $siswa->alamat }}</td></tr>
    </table>

    <h3>A. Nilai Akademik</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                <th>Nilai Akhir</th>
                <th>Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rapor_akhir as $index => $rapor)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $rapor->mapel->nama_mapel ?? '-' }}</td>
                <td class="text-center">{{ $rapor->nilai_akhir }}</td>
                <td>{{ $rapor->deskripsi_capaian }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>B. Praktik Kerja Lapangan (PKL)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mitra DUDI</th>
                <th>Lokasi</th>
                <th>Lama (Bulan)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pkl as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->dudi->nama_dudi ?? '-' }}</td>
                <td>{{ $p->lokasi }}</td>
                <td class="text-center">{{ $p->lama_bulan }}</td>
                <td>{{ $p->keterangan }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">-</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>C. Ekstrakurikuler</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kegiatan Ekstrakurikuler</th>
                <th>Nilai</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ekskul as $index => $e)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $e->ekskul->nama_ekskul ?? '-' }}</td>
                <td class="text-center">{{ $e->nilai_huruf }}</td>
                <td>{{ $e->deskripsi_dapodik }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">-</td></tr>
            @endforelse
        </tbody>
    </table>

    <script>window.print();</script>
</body>
</html>
