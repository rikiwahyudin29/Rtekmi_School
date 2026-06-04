<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Berita;
use App\Models\WebProfil;
use Illuminate\Support\Facades\DB;

class BeritaPublicController extends Controller
{
    public function index()
    {
        $webProfil = WebProfil::first();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first() ?? (object)[];
        $dataWeb = (object) array_merge((array) $sekolah, $webProfil ? $webProfil->toArray() : []);

        // Berita headline (terbaru)
        $headline = Berita::where('is_published', 1)->orderBy('created_at', 'DESC')->first();
        
        // Berita grid (selain headline)
        $berita = Berita::where('is_published', 1);
        if ($headline) {
            $berita = $berita->where('id', '!=', $headline->id);
        }
        $berita = $berita->orderBy('created_at', 'DESC')->paginate(9);

        // Berita populer (views terbanyak)
        $populer = Berita::where('is_published', 1)->orderBy('views', 'DESC')->limit(5)->get();

        return Inertia::render('Public/Berita/Index', [
            'web' => $dataWeb,
            'headline' => $headline,
            'berita' => $berita,
            'populer' => $populer
        ]);
    }

    public function show($slug)
    {
        $webProfil = WebProfil::first();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first() ?? (object)[];
        $dataWeb = (object) array_merge((array) $sekolah, $webProfil ? $webProfil->toArray() : []);

        $artikel = Berita::where('slug', $slug)->where('is_published', 1)->firstOrFail();
        
        // Increment views
        $artikel->increment('views');

        $beritaTerkait = Berita::where('is_published', 1)
                            ->where('id', '!=', $artikel->id)
                            ->inRandomOrder()
                            ->limit(3)
                            ->get();

        return Inertia::render('Public/Berita/Show', [
            'web' => $dataWeb,
            'artikel' => $artikel,
            'beritaTerkait' => $beritaTerkait
        ]);
    }
}
