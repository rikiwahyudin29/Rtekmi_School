<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Dudi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DudiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Dudi/Index', [
            'dudi' => Dudi::orderBy('id', 'DESC')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_dudi' => 'required|string',
            'bidang_usaha' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/dudi'), $filename);
            $data['logo'] = $filename;
        }

        Dudi::create($data);

        return redirect()->back()->with('message', 'Mitra DUDI berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $dudi = Dudi::findOrFail($id);
        if ($dudi->logo && file_exists(public_path('uploads/dudi/' . $dudi->logo))) {
            unlink(public_path('uploads/dudi/' . $dudi->logo));
        }
        $dudi->delete();
        return redirect()->back()->with('message', 'Mitra DUDI berhasil dihapus.');
    }
}
