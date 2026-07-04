<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Valid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full text-center border-t-8 border-green-500">
        <div class="mx-auto w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-4xl mb-6 shadow-sm border border-green-200">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Dokumen Valid!</h2>
        <p class="text-gray-500 text-sm mb-8">Dokumen ini diterbitkan secara sah dan tercatat di sistem kami.</p>

        <div class="text-left bg-gray-50 border border-gray-100 rounded-2xl p-5 mb-8 space-y-4">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">No Surat</span>
                <span class="block text-gray-900 font-bold mt-0.5">{{ $surat->no_surat }}</span>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Perihal</span>
                <span class="block text-gray-900 font-medium mt-0.5">{{ $surat->perihal }}</span>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Atas Nama</span>
                <span class="block text-gray-900 font-medium mt-0.5">{{ $surat->siswa ? $surat->siswa->nama_lengkap : 'Umum' }}</span>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Terbit</span>
                <span class="block text-gray-900 font-medium mt-0.5">{{ \Carbon\Carbon::parse($surat->tgl_surat)->isoFormat('D MMMM Y') }}</span>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <p class="text-xs text-gray-400 mb-1">Ditandatangani secara digital oleh:</p>
            <p class="font-bold text-gray-900 text-sm">{{ $surat->nama_kepsek }}</p>
            <p class="text-xs text-gray-500">Kepala Sekolah (NIP. {{ $surat->nip }})</p>
        </div>
    </div>
</body>
</html>
