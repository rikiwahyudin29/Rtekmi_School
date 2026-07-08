<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;
use App\Models\User;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $userId = $request->session()->get('temp_user_id');
        if (!$userId) return redirect()->route('login');

        $user = User::find($userId);
        $google2fa = new Google2FA();
        
        $secret = $google2fa->generateSecretKey();
        $request->session()->put('setup_secret', $secret);

        $web = \Illuminate\Support\Facades\DB::table('tbl_sekolah')->first();

        // Configure QR Code URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $web->nama_sekolah ?? config('app.name', 'SIAKAD'),
            $user->nama_lengkap ?? $user->username,
            $secret
        );

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrImage = 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeUrl));

        return Inertia::render('Auth/Setup2FA', [
            'qr_image' => $qrImage,
            'secret' => $secret,
            'web' => $web
        ]);
    }

    public function verify(Request $request)
    {
        if (!$request->session()->get('temp_user_id')) {
            return redirect()->route('login');
        }

        $web = \Illuminate\Support\Facades\DB::table('tbl_sekolah')->first();

        return Inertia::render('Auth/Verify2FA', [
            'web' => $web
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string',
            'mode' => 'required|in:setup,verify'
        ]);

        $userId = $request->session()->get('temp_user_id');
        if (!$userId) return redirect()->route('login');

        $user = User::find($userId);
        $google2fa = new Google2FA();
        $code = $request->input('otp_code');
        $mode = $request->input('mode');

        if ($mode === 'setup') {
            $secret = $request->session()->get('setup_secret');
            if ($google2fa->verifyKey($secret, $code)) {
                $user->update(['google2fa_secret' => $secret]);
                return $this->doLogin($request, $user);
            }
            return back()->withErrors(['otp_code' => 'Kode Setup Salah! Pastikan scan QR dengan benar.']);
        } 
        
        if ($mode === 'verify') {
            if ($google2fa->verifyKey($user->google2fa_secret, $code)) {
                return $this->doLogin($request, $user);
            }
            return back()->withErrors(['otp_code' => 'Kode Authenticator Salah!']);
        }
    }

    private function doLogin(Request $request, $user)
    {
        $remember = $request->session()->get('temp_remember', false);
        $roles = $request->session()->get('temp_roles', []);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $activeRole = 'siswa';
        if (in_array('admin', $roles)) $activeRole = 'admin';
        elseif (in_array('kepsek', $roles)) $activeRole = 'kepsek';
        elseif (in_array('guru', $roles)) $activeRole = 'guru';

        $request->session()->put('role_active', $activeRole);
        $request->session()->put('roles', $roles);
        
        $request->session()->forget(['temp_user_id', 'temp_remember', 'temp_roles', 'setup_secret']);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
