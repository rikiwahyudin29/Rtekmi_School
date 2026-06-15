<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pelengkap Rapor - {{ $siswa->nama_lengkap }}</title>
    <style>
        @page {
            size: {{ request('kertas', 'A4') }};
            margin: {{ request('margin_atas', 20) }}mm {{ request('margin_kanan', 20) }}mm {{ request('margin_bawah', 20) }}mm {{ request('margin_kiri', 20) }}mm;
        }
        body { font-family: 'Times New Roman', Times, serif; margin: 0; padding: 0; background: #fff; font-size: 16px; line-height: 1.2; }
        .page { width: 100%; box-sizing: border-box; background: white; page-break-after: always; position: relative; padding: 1.5cm; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { border: none; box-shadow: none; margin: 0; width: 100%; page-break-after: always; padding: 0; }
        }
        .page:last-child { page-break-after: auto; }.page:last-child { page-break-after: auto; }
        
        .title-center { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; line-height: 1.4; }
        
        /* Table Layouts */
        .table-identitas { width: 80%; margin: 0 auto; border-collapse: collapse; }
        .table-identitas td { padding: 8px 10px; vertical-align: top; }
        .td-label { width: 30%; }
        .td-colon { width: 5%; text-align: center; }
        .td-value { width: 65%; }

        .table-murid { width: 100%; border-collapse: collapse; }
        .table-murid td { padding: 2px 5px; vertical-align: top; }
        .tm-no { width: 5%; text-align: right; padding-right: 15px !important; }
        .tm-label { width: 35%; }
        .tm-colon { width: 5%; text-align: center; }
        .tm-value { width: 55%; }

        /* Pindah Sekolah Table */
        .table-pindah { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-pindah th, .table-pindah td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        .table-pindah th { text-align: center; font-weight: bold; }

        /* Signatures */
        .signature-area { margin-top: 20px; display: flex; justify-content: center; gap: 100px; align-items: flex-end; }
        .signature-box { width: 300px; text-align: left; }
        .signature-box p { margin: 2px 0; }
        .signature-space { height: 60px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-bottom: 2px; }

        .photo-box { width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center; background: #eee; }
        .photo-img { width: 100%; height: 100%; object-fit: cover; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body onload="window.print()">
    @php
        $tgl_ttd = request('tanggal_titimangsa');
        if(!$tgl_ttd) {
            $tgl_ttd = $tahun_ajaran && $tahun_ajaran->tanggal_pembagian ? \Carbon\Carbon::parse($tahun_ajaran->tanggal_pembagian)->locale('id')->isoFormat('D MMMM Y') : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        } else {
            $tgl_ttd = \Carbon\Carbon::parse($tgl_ttd)->locale('id')->isoFormat('D MMMM Y');
        }
    @endphp

    <!-- HALAMAN 1: IDENTITAS SEKOLAH -->
    <div class="page">
        <div class="title-center" style="margin-top: 3cm;">
            SEKOLAH MENENGAH KEJURUAN<br>
            ( SMK )
        </div>

        <table class="table-identitas">
            <tr>
                <td class="td-label">Nama Sekolah</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->nama_sekolah ?? 'SMK BISA' }}</td>
            </tr>
            <tr>
                <td class="td-label">NPSN</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">NIS/NSS/NDS</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->dapodik_id ?? '344021912008' }}</td>
            </tr>
            <tr>
                <td class="td-label">Alamat Sekolah</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">Kelurahan / Desa</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->desa_kelurahan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">Kecamatan</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->kecamatan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">Kota/Kabupaten</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->kabupaten ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">Provinsi</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->provinsi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">Website</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->website ?? '-' }}</td>
            </tr>
            <tr>
                <td class="td-label">E-mail</td>
                <td class="td-colon">:</td>
                <td class="td-value">{{ $sekolah->email ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- HALAMAN 2: IDENTITAS MURID -->
    <div class="page">
        <div class="title-center">
            IDENTITAS MURID
        </div>

        <table class="table-murid">
            <tr>
                <td class="tm-no">1.</td>
                <td class="tm-label">Nama Lengkap</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="tm-no">2.</td>
                <td class="tm-label">Nomor Induk/NISN</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</td>
            </tr>
            <tr>
                <td class="tm-no">3.</td>
                <td class="tm-label">Tempat ,Tanggal Lahir</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">4.</td>
                <td class="tm-label">Jenis Kelamin</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->jk == 'L' || $siswa->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td class="tm-no">5.</td>
                <td class="tm-label">Agama</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->agama ?? 'Islam' }}</td>
            </tr>
            <tr>
                <td class="tm-no">6.</td>
                <td class="tm-label">Status dalam Keluarga</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->status_keluarga ?? 'Anak Kandung' }}</td>
            </tr>
            <tr>
                <td class="tm-no">7.</td>
                <td class="tm-label">Anak ke</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->anak_ke ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">8.</td>
                <td class="tm-label">Alamat Murid</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">9.</td>
                <td class="tm-label">Nomor Telepon Rumah</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->no_hp_siswa ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">10.</td>
                <td class="tm-label">Sekolah Asal</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->sekolah_asal ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">11.</td>
                <td class="tm-label" colspan="3">Diterima di sekolah ini</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">Di kelas</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->diterima_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">Pada tanggal</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->tanggal_diterima ? \Carbon\Carbon::parse($siswa->tanggal_diterima)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">12.</td>
                <td class="tm-label" colspan="3">Nama Orang Tua</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">a. Ayah</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->nama_ayah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">b. Ibu</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->nama_ibu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">13.</td>
                <td class="tm-label">Alamat Orang Tua</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">Nomor Telepon Rumah</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->no_hp_ortu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">14.</td>
                <td class="tm-label" colspan="3">Pekerjaan Orang Tua :</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">a. Ayah</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->pekerjaan_ayah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">b. Ibu</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->pekerjaan_ibu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">15.</td>
                <td class="tm-label">Nama Wali Siswa</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->nama_wali ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">16.</td>
                <td class="tm-label">Alamat Wali Murid</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->alamat_wali ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no"></td>
                <td class="tm-label">Nomor Telepon Rumah</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->no_hp_wali ?? '-' }}</td>
            </tr>
            <tr>
                <td class="tm-no">17.</td>
                <td class="tm-label">Pekerjaan Wali Murid</td>
                <td class="tm-colon">:</td>
                <td class="tm-value">{{ $siswa->pekerjaan_wali ?? '-' }}</td>
            </tr>
        </table>

        <div class="signature-area">
            <div class="photo-box">
                @if($siswa->foto && $siswa->foto != 'default.png')
                    <img src="{{ Storage::url('siswa/'.$siswa->foto) }}" class="photo-img">
                @else
                    <span style="color:#aaa;">3x4</span>
                @endif
            </div>

            <div class="signature-box">
                <p>{{ request('tempat_cetak', $sekolah->kabupaten ?? 'Kabupaten') }}, {{ $tgl_ttd }}</p>
                <p>Kepala Sekolah</p>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $sekolah->nama_kepsek ?? 'Nama Kepala Sekolah' }}</div>
                <p>NIP. {{ $sekolah->nip_kepsek ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- HALAMAN 3: KETERANGAN PINDAH SEKOLAH -->
    <div class="page">
        <div class="title-center" style="margin-bottom: 20px;">
            KETERANGAN PINDAH SEKOLAH
        </div>

        <div style="display: flex; margin-bottom: 10px;">
            <div style="width: 150px;">Nama Murid</div>
            <div style="width: 10px;">:</div>
            <div>{{ $siswa->nama_lengkap }}</div>
        </div>

        <table class="table-pindah">
            <thead>
                <tr>
                    <th colspan="4">KELUAR</th>
                </tr>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 15%;">Kelas yang ditinggalkan</th>
                    <th style="width: 35%;">Sebab-sebab Keluar atau Atas Permintaan (Tertulis)</th>
                    <th style="width: 35%;">Tanda Tangan Kepala Sekolah, Stempel Sekolah, dan Tanda Tangan Orang Tua/Wali</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 3; $i++)
                <tr>
                    <td style="height: 150px;"></td>
                    <td></td>
                    <td></td>
                    <td>
                        <div style="margin-bottom: 30px;">..........................................<br>Kepala Sekolah,</div>
                        <div style="border-bottom: 1px dotted #000; margin-bottom: 10px;"></div>
                        <div style="margin-bottom: 30px;">Orang Tua/Wali,</div>
                        <div style="border-bottom: 1px dotted #000; margin-bottom: 10px;"></div>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

</body>
</html>
