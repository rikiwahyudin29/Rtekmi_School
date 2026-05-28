<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|in:theme-green,theme-blue,theme-purple,theme-orange'
        ]);

        $theme = $request->input('theme');

        DB::table('tbl_pengaturan')->updateOrInsert(
            ['kunci' => 'tema_warna'],
            ['nilai' => $theme]
        );

        return redirect()->back()->with('success', 'Tema warna berhasil diperbarui.');
    }
}
