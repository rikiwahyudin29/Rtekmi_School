<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        
        @php
            $web = \Illuminate\Support\Facades\DB::table('tbl_sekolah')->first();
            $favicon = $web && $web->logo ? (str_contains($web->logo, 'default') ? asset('images/' . $web->logo) : asset('uploads/identitas/' . $web->logo)) : asset('favicon.ico');
            $site_name = $web && $web->nama_sekolah ? $web->nama_sekolah : 'SMKS RIYADHUL JANNAH JALANCAGAK';
        @endphp
        <!-- Favicon Standards for Google Search & Browsers -->
        <link rel="icon" href="{{ $favicon }}">
        <link rel="shortcut icon" href="{{ $favicon }}">
        <link rel="apple-touch-icon" href="{{ $favicon }}">
        <meta itemprop="image" content="{{ $favicon }}">
        
        <!-- SEO for Google Site Name -->
        <meta property="og:site_name" content="{{ $site_name }}">
        <script type="application/ld+json">
        {
          "@@context": "https://schema.org",
          "@@type": "WebSite",
          "name": "{{ $site_name }}",
          "url": "{{ url('/') }}"
        }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
