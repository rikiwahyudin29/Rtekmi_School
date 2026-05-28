<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Gagal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full text-center border-t-8 border-red-500">
        <div class="mx-auto w-20 h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-4xl mb-6 shadow-sm border border-red-200">
            <i class="fas fa-times-circle"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Dokumen Tidak Ditemukan!</h2>
        <p class="text-gray-500 text-sm mb-8">Maaf, kami tidak dapat menemukan catatan untuk dokumen ini di sistem kami. Ada kemungkinan dokumen ini palsu atau telah dihapus.</p>

        <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-left flex gap-4">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl mt-1"></i>
            <div>
                <h4 class="font-bold text-red-800 text-sm">Peringatan Keamanan</h4>
                <p class="text-xs text-red-600 mt-1">Jika Anda menerima dokumen fisik dengan QR Code ini, harap berhati-hati dan segera laporkan ke pihak sekolah.</p>
            </div>
        </div>
    </div>
</body>
</html>
