<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/berita') }}</loc>
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/spmb/register') }}</loc>
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/kelulusan') }}</loc>
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.7</priority>
    </url>

    @foreach($berita as $b)
    <url>
        <loc>{{ url('/berita/' . $b->slug) }}</loc>
        <lastmod>{{ date('c', strtotime($b->updated_at ?? $b->created_at)) }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
