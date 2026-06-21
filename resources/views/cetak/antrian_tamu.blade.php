<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian #{{ str_pad($tamu->no_antrian, 3, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .ticket-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .ticket {
            width: 302px;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }

        .ticket-top {
            background: linear-gradient(135deg, #312e81, #6b21a8, #312e81);
            padding: 22px 16px;
            text-align: center;
            color: white;
        }

        .school-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 4px;
            margin: 0 auto 10px;
            display: block;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .school-name {
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        .school-tagline {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: rgba(255,255,255,0.75);
            margin-top: 4px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .perforated {
            display: flex;
            align-items: center;
            background: #e5e7eb;
            position: relative;
            height: 24px;
        }
        .perforated::before, .perforated::after {
            content: '';
            width: 24px;
            height: 24px;
            background: #e5e7eb;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .perforated::before { left: -12px; }
        .perforated::after { right: -12px; }
        .perforated-line {
            flex: 1;
            border-top: 2px dashed #9ca3af;
            margin: 0 16px;
        }

        .ticket-body {
            padding: 20px 20px 14px;
            text-align: center;
        }

        .label-antrian {
            font-family: Arial, sans-serif;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .number-antrian {
            font-family: Arial, sans-serif;
            font-size: 88px;
            font-weight: 900;
            color: #312e81;
            line-height: 1;
            letter-spacing: -5px;
        }

        .status-badge {
            display: inline-block;
            background: #fef9c3;
            color: #854d0e;
            font-family: Arial, sans-serif;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            margin-top: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid #fde047;
        }

        .info-section {
            padding: 4px 20px 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e5e7eb;
            padding: 7px 0;
            font-size: 10px;
        }

        .info-label {
            font-family: Arial, sans-serif;
            color: #9ca3af;
            font-weight: 700;
            width: 75px;
            flex-shrink: 0;
        }

        .info-value {
            font-family: Arial, sans-serif;
            color: #111827;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }

        .ticket-footer {
            background: #f9fafb;
            border-top: 1px dashed #d1d5db;
            padding: 12px 20px;
            text-align: center;
        }

        .footer-msg {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.7;
        }

        .footer-time {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #9ca3af;
            margin-top: 6px;
        }

        .barcode-area {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 2px;
            padding: 10px 20px 14px;
        }

        .print-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            font-family: Arial, sans-serif;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            border: none;
            transition: all 0.2s;
        }

        .btn-print {
            background: linear-gradient(135deg, #312e81, #6b21a8);
            color: white;
            box-shadow: 0 4px 15px rgba(49,46,129,0.4);
        }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(49,46,129,0.5); }

        .btn-close {
            background: white;
            color: #374151;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-close:hover { transform: translateY(-2px); }

        @media print {
            body { background: white; padding: 0; display: block; }
            .ticket { box-shadow: none; width: 100%; }
            .print-actions { display: none; }
            .ticket-wrapper { gap: 0; }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <div class="ticket">

        <!-- Header -->
        <div class="ticket-top">
            @if($sekolah && $sekolah->logo)
                <img src="{{ asset($sekolah->logo && str_contains($sekolah->logo, 'default') ? 'images/' . $sekolah->logo : 'uploads/identitas/' . $sekolah->logo) }}" class="school-logo" alt="Logo" onerror="this.style.display='none'">
            @endif
            <div class="school-name">{{ $sekolah->nama_sekolah ?? 'SEKOLAH' }}</div>
            <div class="school-tagline">Tiket Antrian Digital</div>
        </div>

        <!-- Perforated -->
        <div class="perforated"><div class="perforated-line"></div></div>

        <!-- Number -->
        <div class="ticket-body">
            <div class="label-antrian">Nomor Antrian</div>
            <div class="number-antrian">{{ str_pad($tamu->no_antrian, 3, '0', STR_PAD_LEFT) }}</div>
            <div class="status-badge">⏳ Menunggu Dipanggil</div>
        </div>

        <!-- Info -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value">{{ strtoupper($tamu->nama_lengkap) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kategori</span>
                <span class="info-value">{{ $tamu->kategori }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Keperluan</span>
                <span class="info-value">{{ \Illuminate\Support\Str::limit($tamu->keperluan, 40) }}</span>
            </div>
            @if($tamu->instansi_asal)
            <div class="info-row">
                <span class="info-label">Instansi</span>
                <span class="info-value">{{ $tamu->instansi_asal }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($tamu->tanggal)->translatedFormat('d M Y') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
            <div class="footer-msg">
                Silakan tunggu panggilan petugas<br>di area lobi / resepsionis sekolah.
            </div>
            <div class="footer-time">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</div>
        </div>

        <!-- Decorative Barcode -->
        <div class="barcode-area">
            @php $bars = [28,14,36,20,28,12,40,18,30,16,36,22,28,14,38,20,26,16,32,24,28,14,36,22,30,18]; @endphp
            @foreach($bars as $h)
                <div style="background:#1f2937; width:2px; height:{{ $h }}px; border-radius:1px;"></div>
            @endforeach
        </div>

    </div>

    <!-- Buttons -->
    <div class="print-actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Tiket</button>
        <button class="btn btn-close" onclick="window.close()">✕ Tutup</button>
    </div>
</div>

<script>
    // Auto print on load
    window.addEventListener('load', function() {
        setTimeout(() => window.print(), 500);
    });
</script>

</body>
</html>
