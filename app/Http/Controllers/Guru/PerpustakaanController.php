<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PerpustakaanController extends Controller
{
    public function index()
    {
        $buku = DB::table('tbl_elibrary')->orderBy('id', 'DESC')->get();

        return Inertia::render('Guru/ELearning/Perpustakaan/Index', [
            'buku' => $buku,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'kategori' => 'required',
            'penulis' => 'required',
            'ebook' => 'required|mimes:pdf|max:10240',
            'cover' => 'nullable|image|max:2048'
        ]);

        $ebookFile = $request->file('ebook');
        $namaPdf = time() . '_' . $ebookFile->getClientOriginalName();
        $ebookFile->move(public_path('uploads/library'), $namaPdf);

        $namaCover = 'default_book.png';
        if ($request->hasFile('cover')) {
            $coverFile = $request->file('cover');
            $namaCover = time() . '_' . $coverFile->getClientOriginalName();
            $coverFile->move(public_path('uploads/library/covers'), $namaCover);
        }

        DB::table('tbl_elibrary')->insert([
            'judul'     => $request->judul,
            'kategori'  => $request->kategori,
            'penulis'   => $request->penulis,
            'file_ebook'=> $namaPdf,
            'cover'     => $namaCover,
            'diakses'   => 0
        ]);

        return back()->with('success', 'Buku berhasil ditambahkan ke rak digital.');
    }

    public function destroy($id)
    {
        $buku = DB::table('tbl_elibrary')->where('id', $id)->first();
        
        if ($buku) {
            $pdfPath = public_path('uploads/library/' . $buku->file_ebook);
            if (File::exists($pdfPath)) {
                File::delete($pdfPath);
            }

            if ($buku->cover && $buku->cover !== 'default_book.png') {
                $coverPath = public_path('uploads/library/covers/' . $buku->cover);
                if (File::exists($coverPath)) {
                    File::delete($coverPath);
                }
            }

            DB::table('tbl_elibrary')->where('id', $id)->delete();
        }

        return back()->with('success', 'Buku berhasil dihapus.');
    }

    public function counter($id)
    {
        DB::table('tbl_elibrary')->where('id', $id)->increment('diakses');
        return response()->json(['status' => 'success']);
    }

    public function baca($id)
    {
        DB::table('tbl_elibrary')->where('id', $id)->increment('diakses');
        $buku = DB::table('tbl_elibrary')->where('id', $id)->first();
        
        return Inertia::render('Guru/ELearning/Perpustakaan/Baca', [
            'buku' => $buku
        ]);
    }
}
