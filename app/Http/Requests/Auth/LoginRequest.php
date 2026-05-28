<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $loginId = $this->input('login_id');
        $password = $this->input('password');

        // Find user by username or email
        $user = \App\Models\User::where('username', $loginId)
                    ->orWhere('email', $loginId)
                    ->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // Check password (Hashed or Plain text fallback like CI4)
        $verify_pass = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        if (!$verify_pass && $password === $user->password) {
            $verify_pass = true;
            // Optionally, we could hash the plain password here to migrate it automatically
            // $user->update(['password' => \Illuminate\Support\Facades\Hash::make($password)]);
        }

        if (!$verify_pass) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // Return the user so Controller can handle 2FA
        RateLimiter::clear($this->throttleKey());
        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login_id')).'|'.$this->ip());
    }
}
