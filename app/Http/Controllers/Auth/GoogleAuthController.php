<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Google\Service\Oauth2;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    protected $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->googleClient->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->googleClient->setRedirectUri(route('google.callback'));
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }

    public function redirect()
    {
        return redirect()->away($this->googleClient->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->withErrors(['login_id' => 'Gagal login dari Google.']);
        }

        $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);

        if (!isset($token['error'])) {
            $this->googleClient->setAccessToken($token['access_token']);
            $googleService = new Oauth2($this->googleClient);
            $googleUser = $googleService->userinfo->get();

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $roles = $user->roles->pluck('role_key')->toArray();
                if (empty($roles)) {
                    return redirect()->route('login')->withErrors(['login_id' => 'Akun Google terdaftar, namun tidak memiliki Jabatan.']);
                }

                $isOnlySiswa = (count($roles) == 1 && $roles[0] === 'siswa');

                if ($isOnlySiswa) {
                    Auth::login($user, true);
                    $request->session()->regenerate();
                    $request->session()->put('role_active', 'siswa');
                    return redirect()->intended(route('dashboard', absolute: false));
                }

                // Arahkan ke 2FA
                $request->session()->put('temp_user_id', $user->id);
                $request->session()->put('temp_remember', true);
                $request->session()->put('temp_roles', $roles);
                
                if (empty($user->google2fa_secret)) {
                    return redirect()->route('2fa.setup');
                } else {
                    return redirect()->route('2fa.verify');
                }
            } else {
                return redirect()->route('login')->withErrors(['login_id' => 'Email Google tidak terdaftar di sistem SIAKAD.']);
            }
        }
        
        return redirect()->route('login')->withErrors(['login_id' => 'Gagal autentikasi Google.']);
    }
}
