<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Galeri;
use Illuminate\Support\Str;

class GaleriController extends Controller
{
    public function index()
    {
        $galeri = Galeri::orderBy('created_at', 'DESC')->paginate(12);
        return Inertia::render('Admin/Web/Galeri/Index', [
            'galeri' => $galeri
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:50',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $data = $request->only(['judul', 'kategori']);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/galeri'), $filename);
            $data['gambar'] = $filename;
        }

        Galeri::create($data);

        return redirect()->route('admin.web.galeri.index')->with('message', 'Foto berhasil diunggah ke Galeri.');
    }

    public function destroy($id)
    {
        $galeri = Galeri::findOrFail($id);
        
        if ($galeri->gambar && file_exists(public_path('uploads/galeri/' . $galeri->gambar))) {
            unlink(public_path('uploads/galeri/' . $galeri->gambar));
        }

        $galeri->delete();
        return redirect()->route('admin.web.galeri.index')->with('message', 'Foto berhasil dihapus.');
    }
}
