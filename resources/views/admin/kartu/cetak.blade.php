@php
    $pathLogo = 'uploads/identitas/' . ($sekolah->logo ?? 'default.png');
    if (!file_exists(public_path($pathLogo)) || empty($sekolah->logo)) {
        $pathLogo = 'assets/img/logo.svg'; 
    }

    $is_guru = isset($tipe) && $tipe === 'guru'; 
    $nama_kelas_header = $is_guru ? 'GURU & STAFF' : ($kelas ?? '-');

    // TANGKAP MODE DARI URL (Default Portrait)
    $mode = request()->query('mode', 'portrait');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak Kartu - {{ $nama_kelas_header }}</title>
    <style>
        /* RESET & BASE STYLE */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e2e8f0; -webkit-print-color-adjust: exact; margin: 0; padding: 20px; }
        .page-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        
        /* TOMBOL MENU (HANYA TAMPIL DI LAYAR) */
        .no-print { text-align: center; margin-bottom: 25px; width: 100%; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin: 0 5px; transition: all 0.2s; }
        .btn-print { background: #2563eb; color: white; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-mode { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .btn-mode:hover { background: #e2e8f0; }
        .btn-mode.active { background: #0f172a; color: white; border-color: #0f172a; }

        /* ====================================================
           1. DESAIN PORTRAIT (54mm x 85.6mm)
           ==================================================== */
        .card.portrait {
            width: 214px; height: 338px;
            background: {{ $is_guru ? 'linear-gradient(150deg, #065f46 0%, #10b981 100%)' : 'linear-gradient(150deg, #1e3a8a 0%, #3b82f6 100%)' }}; 
            border-radius: 12px; position: relative; overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); color: white;
            border: 1px solid rgba(255,255,255,0.2); display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .card.portrait::before { content: ''; position: absolute; top: -80px; right: -80px; width: 220px; height: 220px; background: rgba(255,255,255,0.08); border-radius: 50%; pointer-events: none; z-index: 1; }
        .card.portrait::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; z-index: 1; }
        
        .card.portrait .header { width: 100%; padding: 15px 10px 10px; position: relative; z-index: 2; }
        .card.portrait .logo-img { width: 45px; height: 45px; object-fit: contain; background: #fff; border-radius: 50%; padding: 3px; margin-bottom: 5px; }
        .card.portrait .school-name { font-size: 11px; font-weight: 900; text-transform: uppercase; line-height: 1.2; text-shadow: 1px 1px 2px rgba(0,0,0,0.4); }
        .card.portrait .card-type { font-size: 8px; letter-spacing: 2px; margin-top: 3px; font-weight: bold; background: rgba(255,255,255,0.2); display: inline-block; padding: 2px 8px; border-radius: 10px; }
        
        .card.portrait .photo-frame { width: 80px; height: 100px; background: #fff; border-radius: 8px; padding: 4px; z-index: 2; margin: 5px 0 10px; }
        .card.portrait .student-info { z-index: 2; width: 90%; display: flex; flex-direction: column; align-items: center; }
        .card.portrait .s-name { font-size: 13px; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; line-height: 1.2; width: 100%; }
        .card.portrait .name-guru { font-size: 11.5px; } 
        .card.portrait .s-nis { font-family: monospace; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-bottom: 8px; }
        
        .card.portrait .meta-container { width: 100%; background: rgba(255,255,255,0.1); border-radius: 6px; padding: 5px; text-align: left; }
        .card.portrait .meta-row { font-size: 8px; margin-bottom: 2px; display: flex; align-items: flex-start; }
        .card.portrait .meta-label { width: 45px; font-weight: bold; opacity: 0.9; }
        
        .card.portrait .qr-box { position: absolute; bottom: 12px; right: 12px; background: white; padding: 3px; border-radius: 6px; z-index: 2; }
        .card.portrait .qr-code { width: 35px; height: 35px; display: block; }
        .card.portrait .footer-stripe { position: absolute; bottom: 0; left: 0; right: 0; height: 6px; background: #fcd34d; z-index: 3; }

        /* ====================================================
           2. DESAIN LANDSCAPE (85.6mm x 54mm)
           ==================================================== */
        .card.landscape {
            width: 324px; height: 204px;
            background: {{ $is_guru ? 'linear-gradient(135deg, #047857 0%, #10b981 100%)' : 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)' }}; 
            border-radius: 10px; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.15); color: white; border: 1px solid rgba(255,255,255,0.2);
        }
        .card.landscape::before { content: ''; position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: rgba(255,255,255,0.1); border-radius: 50%; pointer-events: none; }
        .card.landscape::after { content: ''; position: absolute; bottom: -40px; left: -40px; width: 140px; height: 140px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
        
        .card.landscape .header { padding: 10px 15px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); z-index: 2; background: rgba(0,0,0,0.1); }
        .card.landscape .logo-img { width: 35px; height: 35px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; }
        .card.landscape .school-info { flex: 1; }
        .card.landscape .school-name { font-size: 11px; font-weight: 800; text-transform: uppercase; line-height: 1.1; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); }
        .card.landscape .card-type { font-size: 8px; opacity: 0.9; letter-spacing: 2px; margin-top: 2px; font-weight: 600; text-transform: uppercase; }
        
        .card.landscape .content { display: flex; padding: 12px 15px; gap: 12px; align-items: flex-start; z-index: 2; }
        .card.landscape .photo-frame { width: 70px; height: 90px; background: #fff; border-radius: 6px; padding: 3px; }
        .card.landscape .student-info { flex: 1; }
        .card.landscape .s-name { font-size: 13px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
        .card.landscape .name-guru { font-size: 11px; }
        .card.landscape .s-nis { font-family: monospace; background: rgba(0,0,0,0.25); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; display: inline-block; margin-bottom: 6px; }
        
        .card.landscape .meta-row { font-size: 9px; margin-bottom: 2px; display: flex; opacity: 0.95; }
        .card.landscape .meta-label { width: 45px; font-weight: 600; opacity: 0.8; }
        .card.landscape .meta-val { font-weight: 600; }
        
        .card.landscape .qr-box { position: absolute; bottom: 10px; right: 10px; background: white; padding: 2px; border-radius: 4px; z-index: 2; }
        .card.landscape .qr-code { width: 40px; height: 40px; display: block; }
        .card.landscape .footer-stripe { position: absolute; bottom: 0; left: 0; right: 0; height: 6px; background: #fcd34d; }

        /* MODE PRINT GLOBAL */
        .photo-img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .page-container { gap: 10px; padding: 10px; }
            .card { border: 1px solid #ccc; box-shadow: none; -webkit-print-color-adjust: exact; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="ubahMode('portrait')" class="btn btn-mode {{ $mode == 'portrait' ? 'active' : '' }}">
            📱 Mode Portrait
        </button>
        <button onclick="ubahMode('landscape')" class="btn btn-mode {{ $mode == 'landscape' ? 'active' : '' }}">
            💳 Mode Landscape
        </button>
        <button onclick="window.print()" class="btn btn-print">
            🖨️ CETAK KARTU
        </button>
        <div style="font-size: 12px; margin-top: 10px; color: #666; font-weight: bold;">
            *Pastikan setting "Background Graphics" / "Cetak Latar Belakang" dicentang saat mem-print.
        </div>
    </div>

    <div class="page-container">
        @foreach($peserta as $s)
            @php
                // Data Olahan (Bisa Dipakai Kedua Mode)
                $folder = $is_guru ? 'guru' : 'siswa';
                $nama_encode = urlencode($s->nama_lengkap);
                $foto = !empty($s->foto) && file_exists(public_path("uploads/{$folder}/" . $s->foto)) 
                        ? asset("uploads/{$folder}/" . $s->foto) 
                        : "https://ui-avatars.com/api/?name={$nama_encode}&background=random&size=128"; 
                
                $qr_data = $is_guru ? (!empty($s->nik) ? $s->nik : 'GURU-'.$s->id) : $s->nis;

                // Format Nama Guru
                $gelar_dpn = !empty($s->gelar_depan) ? $s->gelar_depan.' ' : '';
                $gelar_blk = !empty($s->gelar_belakang) ? ', '.$s->gelar_belakang : '';
                $nama_full = $is_guru ? ($gelar_dpn . $s->nama_lengkap . $gelar_blk) : $s->nama_lengkap;
            @endphp

            <div class="card {{ $mode }}">
                
                @if($mode == 'portrait')
                    <div class="header">
                        <img src="{{ asset($pathLogo) }}" class="logo-img">
                        <div class="school-name">{{ $sekolah->nama_sekolah ?? 'SMK' }}</div>
                        <div class="card-type">{{ $is_guru ? 'KARTU IDENTITAS GURU' : 'KARTU TANDA PELAJAR' }}</div>
                    </div>
                    <div class="photo-frame"><img src="{{ $foto }}" class="photo-img"></div>
                    <div class="student-info">
                        <div class="s-name {{ $is_guru ? 'name-guru' : '' }}">{{ strtoupper($nama_full) }}</div>
                        <div class="s-nis">{{ $is_guru ? (!empty($s->nik) ? $s->nik : '-') : $s->nis }}</div>
                        <div class="meta-container">
                            @if($is_guru)
                                <div class="meta-row"><span class="meta-label">NUPTK</span><span class="meta-val">: {{ !empty($s->nuptk) ? $s->nuptk : '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Status</span><span class="meta-val">: {{ !empty($s->status_guru) ? strtoupper($s->status_guru) : '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Berlaku</span><span class="meta-val">: Selama Menjabat</span></div>
                            @else
                                <div class="meta-row"><span class="meta-label">Kelas</span><span class="meta-val">: {{ $kelas ?? '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">L/P</span><span class="meta-val">: {{ $s->jenis_kelamin ?? '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Berlaku</span><span class="meta-val">: {{ date('Y') + 3 }}</span></div>
                            @endif
                        </div>
                    </div>
                    <div class="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qr_data) }}" class="qr-code"></div>
                    <div class="footer-stripe"></div>

                @else
                    <div class="header">
                        <img src="{{ asset($pathLogo) }}" class="logo-img">
                        <div class="school-info">
                            <div class="school-name">{{ $sekolah->nama_sekolah ?? 'SMK' }}</div>
                            <div class="card-type">{{ $is_guru ? 'KARTU IDENTITAS GURU' : 'KARTU TANDA PELAJAR' }}</div>
                        </div>
                    </div>
                    <div class="content">
                        <div class="photo-frame"><img src="{{ $foto }}" class="photo-img"></div>
                        <div class="student-info">
                            <div class="s-name {{ $is_guru ? 'name-guru' : '' }}">{{ strtoupper($nama_full) }}</div>
                            <div class="s-nis">{{ $is_guru ? (!empty($s->nik) ? $s->nik : '-') : $s->nis }}</div>
                            @if($is_guru)
                                <div class="meta-row"><span class="meta-label">NUPTK</span><span class="meta-val">: {{ !empty($s->nuptk) ? $s->nuptk : '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Status</span><span class="meta-val">: {{ !empty($s->status_guru) ? strtoupper($s->status_guru) : '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Berlaku</span><span class="meta-val">: Selama Menjabat</span></div>
                            @else
                                <div class="meta-row"><span class="meta-label">Kelas</span><span class="meta-val">: {{ $kelas ?? '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">L/P</span><span class="meta-val">: {{ $s->jenis_kelamin ?? '-' }}</span></div>
                                <div class="meta-row"><span class="meta-label">Berlaku</span><span class="meta-val">: {{ date('Y') + 3 }}</span></div>
                            @endif
                        </div>
                    </div>
                    <div class="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qr_data) }}" class="qr-code"></div>
                    <div class="footer-stripe"></div>
                @endif

            </div>
        @endforeach
    </div>

    <script>
        function ubahMode(modePilihan) {
            const url = new URL(window.location.href);
            url.searchParams.set('mode', modePilihan);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
