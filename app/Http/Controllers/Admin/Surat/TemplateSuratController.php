<?php

namespace App\Http\Controllers\Admin\Surat;

use App\Http\Controllers\Controller;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateSuratController extends Controller
{
    public function index()
    {
        $templates = SuratTemplate::orderBy('id', 'desc')->paginate(10);
        return Inertia::render('Admin/Surat/Template/Index', [
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'format_nomor' => 'required|string|max:255',
            'isi_html' => 'required|string'
        ]);

        SuratTemplate::create([
            'nama_template' => $request->nama_template,
            'format_nomor' => $request->format_nomor,
            'isi_html' => $request->isi_html
        ]);

        return redirect()->back()->with('success', 'Template berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'format_nomor' => 'required|string|max:255',
            'isi_html' => 'required|string'
        ]);

        $template = SuratTemplate::findOrFail($id);
        $template->update([
            'nama_template' => $request->nama_template,
            'format_nomor' => $request->format_nomor,
            'isi_html' => $request->isi_html
        ]);

        return redirect()->back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy($id)
    {
        SuratTemplate::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Template berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tbl_surat_template,id'
        ]);

        SuratTemplate::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', count($request->ids) . ' Template berhasil dihapus.');
    }
}
