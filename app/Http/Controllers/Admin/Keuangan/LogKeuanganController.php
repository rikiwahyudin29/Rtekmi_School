<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\LogKeuangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogKeuanganController extends Controller
{
    public function index()
    {
        // Ambil 200 data log terakhir
        $logs = LogKeuangan::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return Inertia::render('Admin/Keuangan/Log/Index', [
            'logs' => $logs
        ]);
    }
}
