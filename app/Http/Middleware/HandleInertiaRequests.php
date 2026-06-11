<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $web = \Illuminate\Support\Facades\DB::table('tbl_sekolah')->first();
        $tema = \Illuminate\Support\Facades\DB::table('tbl_pengaturan')->where('kunci', 'tema_warna')->first();

        $user = $request->user();
        $avatar_url = null;
        if ($user) {
            // Coba ambil dari tabel guru terlebih dahulu
            $guru = \Illuminate\Support\Facades\DB::table('tbl_guru')->where('user_id', $user->id)->first();
            if ($guru && $guru->foto && $guru->foto !== 'default.png') {
                $avatar_url = asset('uploads/guru/' . $guru->foto);
            } else {
                // Jika tidak ada di tabel guru, coba cek di tabel siswa
                $siswa = \Illuminate\Support\Facades\DB::table('tbl_siswa')->where('user_id', $user->id)->first();
                if ($siswa && $siswa->foto && $siswa->foto !== 'default.png') {
                    $avatar_url = asset('uploads/siswa/' . $siswa->foto);
                }
            }
        }

        $redis_status = false;
        if (config('cache.default') === 'redis') {
            try {
                \Illuminate\Support\Facades\Redis::ping();
                $redis_status = true;
            } catch (\Throwable $e) {
                $redis_status = false;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), ['avatar_url' => $avatar_url]) : null,
                'role' => $request->session()->get('role_active', 'guest'),
                'roles' => $request->session()->get('roles', []),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'detail_gagal' => fn () => $request->session()->get('detail_gagal'),
            ],
            'web_settings' => $web,
            'theme' => $tema ? $tema->nilai : 'theme-green',
            'redis_status' => $redis_status,
        ];
    }
}
