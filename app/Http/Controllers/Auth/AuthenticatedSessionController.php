<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $web = \Illuminate\Support\Facades\DB::table('tbl_sekolah')->first();

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'web' => $web
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validasi Kredensial & Ambil User
        $user = $request->authenticate();

        $roles = $user->roles->pluck('role_key')->toArray();
        if (empty($roles)) {
            if ($user->role === 'siswa') {
                $roles = ['siswa'];
            } else {
                return back()->withErrors(['login_id' => 'Akun valid tapi tidak memiliki Jabatan.']);
            }
        }

        $isOnlySiswa = (count($roles) == 1 && $roles[0] === 'siswa');

        // 3. Jika hanya siswa, langsung login tanpa 2FA
        if ($isOnlySiswa) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->put('role_active', 'siswa');
            return redirect()->intended(route('dashboard', absolute: false)); // Siswa dashboard path bisa disesuaikan
        }

        // 4. Selain siswa (Guru/Admin/Kepsek), arahkan ke 2FA
        $request->session()->put('temp_user_id', $user->id);
        $request->session()->put('temp_remember', $request->boolean('remember'));
        $request->session()->put('temp_roles', $roles);
        
        if (empty($user->google2fa_secret)) {
            return redirect()->route('2fa.setup');
        } else {
            return redirect()->route('2fa.verify');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
