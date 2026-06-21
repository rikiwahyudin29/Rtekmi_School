<?php
// Konversi Foto Siswa
$pathFoto = public_path('uploads/siswa/' . ($siswa->foto ?? 'default_siswa.png'));
$fotoBase64 = '';
if(file_exists($pathFoto)) {
    $typeF = pathinfo($pathFoto, PATHINFO_EXTENSION);
    $dataFoto = file_get_contents($pathFoto);
    $fotoBase64 = 'data:image/' . $typeF . ';base64,' . base64_encode($dataFoto);
}

if (!function_exists('uc')) {
    function uc($string) { return ucwords(strtolower(trim($string ?? '-'))); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Induk - {{ $siswa->nama_lengkap }}</title>
    <style>
        /* ================= SETUP KERTAS ================= */
        @page { margin: 35px 45px; size: A4 portrait; }
        body { margin: 0; padding: 0; font-family: "Times New Roman", Times, serif; font-size: 10pt; line-height: 1.3; color: #000; }
        
        /* ================= HEADER & FOTO PRESISI ================= */
        .header-container { position: relative; width: 100%; min-height: 140px; margin-bottom: 10px; }
        .judul-utama { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 25px; }
        
        table.header-bi { width: 75%; }
        table.header-bi td { padding: 3px; vertical-align: bottom; font-size: 10pt; }
        .titik-titik { border-bottom: 1px dotted #000; width: 100%; display: inline-block; }
        
        /* FOTO TERKUNCI DI POJOK KANAN ATAS */
        .box-foto { position: absolute; top: 0px; right: 0px; width: 3cm; height: 4cm; border: 1px solid #000; text-align: center; font-size: 9pt; }
        .teks-foto { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }

        /* ================= LAYOUT FORM BUKU INDUK ================= */
        table.form-bi { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table.form-bi td { padding: 3px 0; vertical-align: top; }
        .sub-judul { font-size: 11pt; font-weight: bold; padding-top: 8px; padding-bottom: 4px; text-transform: uppercase; }
        .nomor { width: 25px; text-align: right; padding-right: 5px !important; }
        .label-teks { width: 240px; }
        .titik-dua { width: 15px; text-align: center; }
        .isi-teks { border-bottom: 1px dotted #000; font-weight: bold; }

        /* ================= TABEL GRID (UNTUK G-J) ================= */
        table.tabel-grid { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; font-size: 9pt; }
        table.tabel-grid th, table.tabel-grid td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        table.tabel-grid th { font-weight: bold; }
        .text-left { text-align: left !important; }
        
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="judul-utama">LEMBAR BUKU INDUK SISWA SMK</div>
        
        <table class="header-bi">
            <tr>
                <td width="30%">Nama Peserta Didik</td><td width="2%">:</td>
                <td width="68%"><span class="titik-titik" style="font-weight: bold; text-transform: uppercase;">{{ $siswa->nama_lengkap }}</span></td>
            </tr>
            <tr><td>NIS / NISN</td><td>:</td><td><span class="titik-titik" style="font-weight: bold;">{{ $siswa->nis }} / {{ $siswa->nisn }}</span></td></tr>
            <tr><td>Nama Sekolah</td><td>:</td><td><span class="titik-titik">{{ $sekolah->nama_sekolah ?? '-' }}</span></td></tr>
            <tr><td>Alamat Sekolah</td><td>:</td><td><span class="titik-titik">{{ $sekolah->alamat ?? '-' }}</span></td></tr>
            <tr><td>Kompetensi Keahlian</td><td>:</td><td><span class="titik-titik">{{ $siswa->nama_jurusan ?? '-' }}</span></td></tr>
        </table>

        <div class="box-foto">
            @if($fotoBase64)
                <img src="{{ $fotoBase64 }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <span class="teks-foto">Pas Foto<br>3 x 4</span>
            @endif
        </div>
    </div>

    <div class="sub-judul">A. KETERANGAN PRIBADI</div>
    <table class="form-bi">
        <tr><td class="nomor">1.</td><td class="label-teks">Nama Peserta Didik</td><td class="titik-dua"></td><td class="isi-teks" style="border:none;"></td></tr>
        <tr><td></td><td style="padding-left: 15px;">- Nama Lengkap</td><td class="titik-dua">:</td><td class="isi-teks" style="text-transform: uppercase;">{{ $siswa->nama_lengkap }}</td></tr>
        <tr><td></td><td style="padding-left: 15px;">- Nama Panggilan</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->nama_panggilan ?? '-') }}</td></tr>
        <tr><td class="nomor">2.</td><td class="label-teks">Jenis Kelamin</td><td class="titik-dua">:</td><td class="isi-teks">{{ $siswa->jk == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td></tr>
        <tr><td class="nomor">3.</td><td class="label-teks">Tempat Lahir</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($siswa->tempat_lahir) }}</td></tr>
        <tr><td class="nomor">4.</td><td class="label-teks">Tanggal Lahir</td><td class="titik-dua">:</td><td class="isi-teks">{{ date('d F Y', strtotime($siswa->tanggal_lahir)) }}</td></tr>
        <tr><td class="nomor">5.</td><td class="label-teks">Agama</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($siswa->agama) }}</td></tr>
        <tr><td class="nomor">6.</td><td class="label-teks">Kewarganegaraan</td><td class="titik-dua">:</td><td class="isi-teks">Indonesia</td></tr>
        <tr><td class="nomor">7.</td><td class="label-teks">Anak ke berapa</td><td class="titik-dua">:</td><td class="isi-teks">{{ $siswa->anak_ke ?? '-' }}</td></tr>
        <tr><td class="nomor">8.</td><td class="label-teks">Jumlah Saudara Kandung</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->jml_saudara_kandung ?? '0' }}</td></tr>
        <tr><td class="nomor">9.</td><td class="label-teks">Jumlah Saudara Tiri</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->jml_saudara_tiri ?? '0' }}</td></tr>
        <tr><td class="nomor">10.</td><td class="label-teks">Jumlah Saudara Angkat</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->jml_saudara_angkat ?? '0' }}</td></tr>
        <tr><td class="nomor">11.</td><td class="label-teks">Anak yatim/piatu/yatim piatu</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->status_yatim_piatu ?? '-' }}</td></tr>
        <tr><td class="nomor">12.</td><td class="label-teks">Bahasa sehari-hari di rumah</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->bahasa_sehari_hari ?? 'Indonesia') }}</td></tr>
    </table>

    <div class="sub-judul">B. KETERANGAN TEMPAT TINGGAL</div>
    <table class="form-bi">
        <tr><td class="nomor">13.</td><td class="label-teks">Alamat Lengkap</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($siswa->alamat) }}</td></tr>
        <tr><td class="nomor">14.</td><td class="label-teks">Nomor Telepon / HP</td><td class="titik-dua">:</td><td class="isi-teks">{{ $siswa->no_hp_siswa ?? '-' }}</td></tr>
        <tr><td class="nomor">15.</td><td class="label-teks">Tinggal Bersama</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->tinggal_bersama ?? 'Orang Tua') }}</td></tr>
        <tr><td class="nomor">16.</td><td class="label-teks">Jarak dari tempat tinggal ke sekolah</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->jarak_ke_sekolah ?? '-') }}</td></tr>
        <tr><td class="nomor">17.</td><td class="label-teks">Ke sekolah dengan kendaraan/jalan kaki</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->transportasi ?? '-') }}</td></tr>
    </table>

    <div class="sub-judul">C. KETERANGAN KESEHATAN</div>
    
    <table class="tabel-grid" style="width: 70%; margin-left: 20px;">
        <tr><th width="30%"></th><th width="35%">Pada waktu diterima</th><th width="35%">Pada waktu meninggalkan sekolah</th></tr>
        <tr><td class="text-left">18. Berat Badan</td><td>{{ $detail->berat_badan ?? '-' }} kg</td><td>{{ $detail->berat_meninggalkan ?? '-' }} kg</td></tr>
        <tr><td class="text-left">19. Tinggi Badan</td><td>{{ $detail->tinggi_badan ?? '-' }} cm</td><td>{{ $detail->tinggi_meninggalkan ?? '-' }} cm</td></tr>
    </table>

    <table class="form-bi">
        <tr><td class="nomor">20.</td><td class="label-teks">Golongan Darah</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->gol_darah ?? '-' }}</td></tr>
        <tr><td class="nomor">21.</td><td colspan="3">Penyakit yang pernah diderita: <span class="isi-teks" style="display:inline-block; width: 65%;">{{ uc($detail->penyakit_pernah_diderita ?? '-') }}</span></td></tr>
        <tr><td class="nomor">22.</td><td class="label-teks">Kelainan Jasmaniah / lainnya</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->kelainan_jasmani ?? '-') }}</td></tr>
    </table>


    <div class="page-break"></div>

    <div class="sub-judul">D. KETERANGAN PENDIDIKAN SEBELUMNYA</div>
    <table class="form-bi">
        <tr><td class="nomor">23.</td><td class="label-teks">Asal Sekolah (SMP/MTs)</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($siswa->sekolah_asal) }}</td></tr>
        <tr><td></td><td style="padding-left: 15px;">a. Tanggal dan Nomor STTB/Ijazah</td><td class="titik-dua">:</td>
            <td class="isi-teks">{{ (!empty($detail->tgl_sttb_smp) ? date('d M Y', strtotime($detail->tgl_sttb_smp)) : '-') }} | No: {{ $detail->no_sttb_smp ?? '-' }}</td>
        </tr>
        <tr><td></td><td style="padding-left: 15px;">b. Lama Belajar</td><td class="titik-dua">:</td><td class="isi-teks">{{ $detail->lama_belajar_smp ?? '3 Tahun' }}</td></tr>
        <tr><td class="nomor">24.</td><td class="label-teks">Pindah dari sekolah (Jika Pindahan)</td><td class="titik-dua">:</td><td class="isi-teks">{{ uc($detail->alasan_pindah ?? '-') }}</td></tr>
        <tr><td></td><td style="padding-left: 15px;">- Diterima di sekolah ini tanggal</td><td class="titik-dua">:</td><td class="isi-teks">{{ !empty($detail->tgl_diterima) ? date('d F Y', strtotime($detail->tgl_diterima)) : '-' }}</td></tr>
    </table>

    <div class="sub-judul">E. KETERANGAN TENTANG ORANG TUA KANDUNG</div>
    <table class="tabel-grid">
        <tr><th width="5%">No</th><th width="35%">Orang Tua Kandung</th><th width="30%">Ayah</th><th width="30%">Ibu</th></tr>
        <tr><td>25.</td><td class="text-left">Nama Lengkap</td><td>{{ uc($siswa->nama_ayah) }}</td><td>{{ uc($siswa->nama_ibu) }}</td></tr>
        <tr><td>26.</td><td class="text-left">Kewarganegaraan</td><td>Indonesia</td><td>Indonesia</td></tr>
        <tr><td>27.</td><td class="text-left">Ijazah Terakhir</td><td>{{ uc($detail->pendidikan_ayah ?? '-') }}</td><td>{{ uc($detail->pendidikan_ibu ?? '-') }}</td></tr>
        <tr><td>28.</td><td class="text-left">Pekerjaan</td><td>{{ uc($siswa->pekerjaan_ayah) }}</td><td>{{ uc($siswa->pekerjaan_ibu) }}</td></tr>
        <tr><td>29.</td><td class="text-left">Penghasilan / bulan</td><td>{{ uc($detail->penghasilan_ayah ?? '-') }}</td><td>{{ uc($detail->penghasilan_ibu ?? '-') }}</td></tr>
        <tr><td>30.</td><td class="text-left">Alamat</td><td colspan="2">{{ uc($siswa->alamat) }}</td></tr>
    </table>

    <div class="sub-judul">F. KETERANGAN TENTANG WALI</div>
    <table class="tabel-grid">
        <tr><th width="5%">No</th><th width="35%">Wali</th><th width="30%">Laki-Laki</th><th width="30%">Perempuan</th></tr>
        <tr><td>31.</td><td class="text-left">Nama</td><td>{{ uc($siswa->nama_wali ?: '-') }}</td><td>-</td></tr>
        <tr><td>32.</td><td class="text-left">Ijazah Terakhir</td><td>{{ uc($detail->pendidikan_wali ?? '-') }}</td><td>-</td></tr>
        <tr><td>33.</td><td class="text-left">Pekerjaan</td><td>{{ uc($siswa->pekerjaan_wali ?: '-') }}</td><td>-</td></tr>
        <tr><td>34.</td><td class="text-left">Penghasilan / bulan</td><td>{{ uc($detail->penghasilan_wali ?? '-') }}</td><td>-</td></tr>
        <tr><td>35.</td><td class="text-left">Alamat</td><td colspan="2">{{ uc($siswa->alamat ?? '-') }}</td></tr>
    </table>


    <div class="page-break"></div>

    <div class="sub-judul">G. KETERANGAN TENTANG KEPRIBADIAN</div>
    <table class="form-bi">
        <tr><td class="nomor">36.</td><td class="label-teks" style="width: 150px;">Intelegensi (IQ)</td><td class="titik-dua">:</td><td class="isi-teks">{{ !empty($detail->iq) ? $detail->iq : '.........................................................' }}</td></tr>
        <tr><td></td><td></td><td></td><td style="font-size: 9pt;">(berdasarkan tes pada tanggal {{ !empty($detail->tgl_tes_iq) ? $detail->tgl_tes_iq : '........................ bulan .................... tahun ................' }})</td></tr>
        <tr><td class="nomor">37.</td><td colspan="3">Aspek Kepribadian :</td></tr>
    </table>

    <table class="tabel-grid" style="font-size: 8.5pt;">
        <tr>
            <th rowspan="2" width="3%">No</th>
            <th rowspan="2" width="25%">Aspek yang dinilai</th>
            <th colspan="3">Semester 1</th><th colspan="3">Semester 2</th><th colspan="3">Semester 3</th>
            <th colspan="3">Semester 4</th><th colspan="3">Semester 5</th><th colspan="3">Semester 6</th>
        </tr>
        <tr>
            <th>B</th><th>C</th><th>K</th> <th>B</th><th>C</th><th>K</th> <th>B</th><th>C</th><th>K</th>
            <th>B</th><th>C</th><th>K</th> <th>B</th><th>C</th><th>K</th> <th>B</th><th>C</th><th>K</th>
        </tr>
        @php 
        $aspek = [
            'disiplin' => 'Disiplin / Ketertiban', 
            'kreativitas' => 'Prakarsa / Kreativitas', 
            'tanggung_jawab' => 'Tanggung Jawab', 
            'penyesuaian' => 'Penyesuaian', 
            'emosi' => 'Kemantapan Emosi', 
            'kerjasama' => 'Kerjasama'
        ];
        $no_aspek = 1;
        @endphp
        @foreach($aspek as $key => $asp)
        <tr>
            <td>{{ $no_aspek++ }}.</td><td class="text-left">{{ $asp }}</td>
            @for($i=1; $i<=6; $i++) 
                <td>{{ $kepribadian[$key][$i]['B'] ?? '' }}</td>
                <td>{{ $kepribadian[$key][$i]['C'] ?? '' }}</td>
                <td>{{ $kepribadian[$key][$i]['K'] ?? '' }}</td>
            @endfor
        </tr>
        @endforeach
    </table>

    <table class="form-bi">
        <tr><td class="nomor">38.</td><td colspan="3">Bakat khusus dan prestasi yang menonjol dalam :</td></tr>
    </table>

    <table class="tabel-grid">
        <tr>
            <th width="5%">No</th><th width="15%">SMTH / TH</th><th width="16%">Kesenian</th><th width="16%">Olahraga</th>
            <th width="16%">Kemasyarakatan<br>Organisasi</th><th width="16%">Hasta Karya</th><th width="16%">Karya Tulis</th>
        </tr>
        @for($i=1; $i<=6; $i++)
        <tr>
            <td>{{ $i }}.</td>
            <td style="height: 20px;">{{ $bakat[$i]['smth'] ?? '' }}</td>
            <td>{{ $bakat[$i]['kesenian'] ?? '' }}</td>
            <td>{{ $bakat[$i]['olahraga'] ?? '' }}</td>
            <td>{{ $bakat[$i]['organisasi'] ?? '' }}</td>
            <td>{{ $bakat[$i]['hasta_karya'] ?? '' }}</td>
            <td>{{ $bakat[$i]['karya_tulis'] ?? '' }}</td>
        </tr>
        @endfor
    </table>


<div class="page-break"></div>

    <div class="sub-judul">H. KETERANGAN KEHADIRAN</div>
    <div style="font-size: 10pt; margin-bottom: 5px; margin-left: 25px;">39. Jumlah hari kehadiran tiap semester :</div>
    <table class="tabel-grid" style="width: 95%; margin-left: 25px;">
        <tr>
            <th rowspan="2" width="20%">Semester / Kls</th>
            <th rowspan="2" width="20%">Hadir</th>
            <th colspan="3">Tidak Hadir Karena</th>
            <th rowspan="2" width="20%">Jumlah Hari Belajar<br>Efektif</th>
        </tr>
        <tr>
            <th width="13%">Sakit</th><th width="13%">Izin</th><th width="14%">Alpa</th>
        </tr>
        @for($i=0; $i<6; $i++)
            @php
            $kh = $kehadiran_history[$i] ?? null;
            $smt = $i + 1;
            @endphp
        <tr>
            <td style="height: 25px; font-weight: bold;">Smt {{ $smt }} / {{ $kh ? $kh->nama_kelas : '.....' }}</td>
            <td>-</td>
            <td>{{ $kh ? $kh->sakit : '.....' }}</td>
            <td>{{ $kh ? $kh->izin : '.....' }}</td>
            <td>{{ $kh ? $kh->alpa : '.....' }}</td>
            <td>-</td>
        </tr>
        @endfor
    </table>

    <div class="sub-judul">I. KETERANGAN SEKITAR PERKEMBANGAN PESERTA DIDIK</div>
    <table class="form-bi" style="margin-left: 25px; width: 95%;">
        <tr><td class="nomor" style="width: 15px;">40.</td><td class="label-teks" style="width: 150px;">Tahun Masuk / terdaftar</td><td class="titik-dua">:</td><td class="isi-teks">{{ !empty($detail->tgl_diterima) ? date('Y', strtotime($detail->tgl_diterima)) : '...........................' }}</td></tr>
        <tr><td class="nomor">41.</td><td class="label-teks">Prestasi peserta didik</td><td class="titik-dua">:</td><td class="isi-teks">{{ !empty($detail->prestasi_siswa) ? $detail->prestasi_siswa : '.................................................................................................' }}</td></tr>
    </table>

    <table class="tabel-grid" style="font-size: 8.5pt;">
        <tr>
            <th width="10%">Tahun<br>Pelajaran</th><th width="5%">Kelas</th><th width="5%">Smt.</th>
            <th width="20%">Program<br>Umum</th><th width="8%">Jumlah<br>Mat Pel</th><th width="7%">Nilai<br>Rata²</th>
            <th width="15%">Peringkat<br>Kelas / Rangking</th>
            <th width="15%">Khusus</th><th width="7%">Nilai<br>Rata²</th>
            <th width="8%">Nilai<br>Tinggal<br>Kelas</th>
        </tr>
        @for($yr=0; $yr<3; $yr++)
            @php
            $smt_ganjil = ($yr * 2);
            $smt_genap = ($yr * 2) + 1;
            
            $rg = $rapor_history[$smt_ganjil] ?? null;
            $rgen = $rapor_history[$smt_genap] ?? null;
            $naik = $kenaikan_history[$yr] ?? null;
            @endphp
        <tr>
            <td rowspan="2" style="font-weight: bold;">{{ !empty($detail->tgl_diterima) ? (date('Y', strtotime($detail->tgl_diterima))+$yr) . '/' . (date('Y', strtotime($detail->tgl_diterima))+$yr+1) : '20..../20....' }}</td>
            <td rowspan="2" class="font-bold">{{ $rg ? explode(' ', $rg['nama_kelas'])[0] : '' }}</td>
            <td style="height: 30px;">1</td>
            <td>UMUM</td>
            <td class="text-center font-bold">{{ $rg ? $rg['jml_mapel'] : '' }}</td>
            <td class="text-center font-bold text-blue-800">{{ $rg ? $rg['rata'] : '' }}</td>
            <td rowspan="2" class="text-center font-bold text-lg">{{ $rgen ? $rgen['ranking'] : ($rg ? $rg['ranking'] : '') }}</td>
            <td rowspan="2"></td><td rowspan="2"></td>
            <td rowspan="2" class="text-center font-bold text-[9px]">{{ $naik ? $naik->status : '' }}</td>
        </tr>
        <tr>
            <td style="height: 30px;">2</td>
            <td>UMUM</td>
            <td class="text-center font-bold">{{ $rgen ? $rgen['jml_mapel'] : '' }}</td>
            <td class="text-center font-bold text-blue-800">{{ $rgen ? $rgen['rata'] : '' }}</td>
        </tr>
        @endfor
    </table>

    <table class="form-bi" style="margin-left: 25px; width: 95%;">
        <tr><td class="nomor" style="width: 15px;">42.</td><td class="label-teks" style="width: 220px;">Tahun meninggalkan sekolah<br>dengan alasan</td><td class="titik-dua" style="vertical-align: top;">:</td><td class="isi-teks" style="vertical-align: top;">{{ !empty($detail->tgl_meninggalkan) ? date('Y', strtotime($detail->tgl_meninggalkan)) . ' - ' . $detail->alasan_meninggalkan : '.......................................................................................' }}</td></tr>
        <tr><td class="nomor">43.</td><td class="label-teks">Pindah sekolah ke</td><td class="titik-dua">:</td><td class="isi-teks">{{ !empty($detail->alasan_meninggalkan) && $detail->alasan_meninggalkan == 'Pindah Sekolah' ? '.......................................................................................' : '-' }}</td></tr>
        <tr><td class="nomor">44.</td><td class="label-teks">Melanjutkan pendidikan ke</td><td class="titik-dua">:</td><td class="isi-teks">.......................................................................................</td></tr>
    </table>

    <div class="sub-judul" style="margin-top: 20px;">J. PENERIMAAN BANTUAN / BEASISWA</div>
    <div style="border: 2px solid #000; padding: 20px; font-weight: bold;">
        <table style="width: 100%;">
            @for($i=1; $i<=3; $i++)
            <tr>
                <td width="10%" style="padding: 10px 0;">Tahun</td><td width="2%">:</td><td width="40%" class="isi-teks">{{ $penerimaan[$i]['tahun'] ?? '' }}</td>
                <td width="8%" style="text-align: center;">dari</td><td width="2%">:</td><td width="38%" class="isi-teks">{{ $penerimaan[$i]['dari'] ?? '' }}</td>
            </tr>
            @endfor
        </table>
    </div>

    <table width="100%" style="margin-top: 40px; page-break-inside: avoid;">
        <tr>
            <td width="60%"></td>
            <td width="40%" style="text-align: left;">
                <p>Kab. {{ $sekolah->kabupaten ?? 'Cirebon' }}, {{ date('d F Y') }}</p>
                <p>Kepala Sekolah,</p>
                <br><br><br><br>
                <p><strong><u>{{ strtoupper($sekolah->nama_kepsek ?? 'KEPALA SEKOLAH') }}</u></strong></p>
                <p>NIP. {{ $sekolah->nip_kepsek ?? '-' }}</p>
            </td>
        </tr>
    </table>

</body>
</html>
