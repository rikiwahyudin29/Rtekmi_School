<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nilai Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; margin: 0; padding: 20px; }
        .page { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.5cm; box-sizing: border-box; background: white; border: 1px solid #ccc; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; margin: 0; width: 100%; }
            .page-break { page-break-before: always; }
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.border th, table.border td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .text-center { text-center; }
        .font-bold { font-weight: bold; }
        .header-info td { padding: 3px 0; }
        h3, h4 { margin-bottom: 10px; margin-top: 20px; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <h3 style="text-align: center; text-transform: uppercase;">LAPORAN HASIL BELAJAR (RAPOR)</h3>
        
        <table class="header-info" style="margin-bottom: 20px;">
            <tr>
                <td width="20%">Nama Peserta Didik</td>
                <td width="2%">:</td>
                <td width="48%"><strong>{{ $siswa->nama_lengkap }}</strong></td>
                <td width="15%">Kelas</td>
                <td width="2%">:</td>
                <td width="13%">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <td>NISN / NIS</td>
                <td>:</td>
                <td>{{ $siswa->nisn }} / {{ $siswa->nis ?? '-' }}</td>
                <td>Fase / Semester</td>
                <td>:</td>
                <td>Fase E / {{ $tahun_ajaran && $tahun_ajaran->semester === 'Genap' ? '2 (Genap)' : '1 (Ganjil)' }}</td>
            </tr>
            <tr>
                <td>Sekolah</td>
                <td>:</td>
                <td>{{ $sekolah->nama_sekolah ?? 'SMK N 1 Contoh' }}</td>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td>{{ $tahun_ajaran->tahun_ajaran ?? '2026/2027' }}</td>
            </tr>
        </table>

        <h4>A. Nilai Akademik</h4>
        <table class="border">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th width="5%">No</th>
                    <th width="30%">Mata Pelajaran</th>
                    <th width="10%">Nilai Akhir</th>
                    <th width="55%">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapor_akhir as $index => $rapor)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ $rapor->mapel->nama_mapel ?? '-' }}</td>
                    <td align="center"><strong>{{ $rapor->nilai_akhir }}</strong></td>
                    <td>
                        <div style="margin-bottom: 5px;">{{ $rapor->deskripsi_tertinggi }}</div>
                        <div>{{ $rapor->deskripsi_terendah }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" align="center">Data Nilai Belum Tersedia</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h4>B. Praktik Kerja Lapangan</h4>
        <table class="border">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th width="5%">No</th>
                    <th width="25%">Mitra DU/DI</th>
                    <th width="20%">Lokasi</th>
                    <th width="10%">Lamanya</th>
                    <th width="40%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pkl as $index => $p)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ $p->dudi->nama_dudi ?? 'Mitra Eksternal' }}</td>
                    <td>{{ $p->lokasi }}</td>
                    <td align="center">{{ $p->lama_bulan }} Bulan</td>
                    <td>{{ $p->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" align="center">Tidak ada data Praktik Kerja Lapangan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h4>C. Ketidakhadiran</h4>
        <table class="border" style="width: 50%;">
            <tr>
                <td width="60%">Sakit</td>
                <td width="40%" align="center">{{ $kehadiran->sakit ?? 0 }} hari</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td align="center">{{ $kehadiran->izin ?? 0 }} hari</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan</td>
                <td align="center">{{ $kehadiran->tanpa_keterangan ?? 0 }} hari</td>
            </tr>
        </table>

        <h4>D. Catatan Wali Kelas</h4>
        <div style="border: 1px solid #000; padding: 10px; min-height: 60px;">
            {{ $catatan->catatan ?? '-' }}
        </div>

        <table style="margin-top: 50px; width: 100%;">
            <tr>
                <td width="30%" align="center">
                    Mengetahui,<br>
                    Orang Tua/Wali<br><br><br><br>
                    (..................................)
                </td>
                <td width="40%"></td>
                <td width="30%" align="center">
                    {{ $sekolah->kabupaten ?? 'Bandung' }}, {{ $tanggal_rapor }}<br>
                    Wali Kelas<br><br><br><br>
                    <strong><u>{{ $siswa->kelas->waliKelas->nama_lengkap ?? 'Nama Wali Kelas S.Pd.' }}</u></strong><br>
                    NIP. {{ $siswa->kelas->waliKelas->nip ?? '-' }}
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center" style="padding-top: 30px;">
                    Mengetahui,<br>
                    Kepala Sekolah<br><br><br><br>
                    <strong><u>{{ $sekolah->nama_kepsek ?? 'Kepala Sekolah M.Pd.' }}</u></strong><br>
                    NIP. {{ $sekolah->nip_kepsek ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
