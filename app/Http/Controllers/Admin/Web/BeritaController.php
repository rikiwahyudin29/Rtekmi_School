<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Berita;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::orderBy('created_at', 'DESC')->paginate(10);
        return Inertia::render('Admin/Web/Berita/Index', [
            'berita' => $berita
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Web/Berita/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'boolean'
        ]);

        $data = $request->only(['judul', 'isi']);
        $data['isi'] = \App\Helpers\SecurityHelper::cleanRichText($data['isi']);
        $data['slug'] = Str::slug($request->judul) . '-' . time();
        $data['penulis'] = auth()->user()->nama ?? 'Admin';
        $data['is_published'] = $request->input('is_published', true);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/berita'), $filename);
            $data['gambar'] = $filename;
        }

        Berita::create($data);

        return redirect()->route('admin.web.berita.index')->with('message', 'Berita berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $berita = Berita::findOrFail($id);
        return Inertia::render('Admin/Web/Berita/Form', [
            'berita' => $berita
        ]);
    }

    public function update(Request $request, $id)
    {
        $berita = Berita::findOrFail($id);
        
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'boolean'
        ]);

        $data = $request->only(['judul', 'isi']);
        $data['isi'] = \App\Helpers\SecurityHelper::cleanRichText($data['isi']);
        $data['is_published'] = $request->input('is_published', true);

        // Update slug only if title changes significantly (optional, maybe keep old slug to preserve SEO)
        // We will just keep the old slug unless requested otherwise.

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/berita'), $filename);
            
            // Delete old image
            if ($berita->gambar && $berita->gambar !== 'default.jpg' && file_exists(public_path('uploads/berita/' . $berita->gambar))) {
                unlink(public_path('uploads/berita/' . $berita->gambar));
            }

            $data['gambar'] = $filename;
        }

        $berita->update($data);

        return redirect()->route('admin.web.berita.index')->with('message', 'Berita berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);
        
        if ($berita->gambar && $berita->gambar !== 'default.jpg' && file_exists(public_path('uploads/berita/' . $berita->gambar))) {
            unlink(public_path('uploads/berita/' . $berita->gambar));
        }

        $berita->delete();
        return redirect()->route('admin.web.berita.index')->with('message', 'Berita berhasil dihapus.');
    }
}
