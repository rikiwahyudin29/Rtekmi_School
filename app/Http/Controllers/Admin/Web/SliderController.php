<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Slider/Index', [
            'sliders' => Slider::orderBy('urutan', 'ASC')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/slider'), $filename);
            $data['gambar'] = $filename;
        }

        Slider::create($data);

        return redirect()->back()->with('message', 'Slider berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        if ($slider->gambar && file_exists(public_path('uploads/slider/' . $slider->gambar))) {
            unlink(public_path('uploads/slider/' . $slider->gambar));
        }
        $slider->delete();
        return redirect()->back()->with('message', 'Slider berhasil dihapus.');
    }
}
