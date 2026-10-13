<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FirebaseUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /** SRS-003: login dengan opsi "Remember Me". */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower($data['email']);
        $key   = 'login:' . sha1($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $guard    = Auth::guard('web');
        $provider = $guard->getProvider();

        /** @var FirebaseUser|null $user */
        $user = $provider->retrieveByCredentials(['email' => $email]);

        if (! $user || ! $provider->validateCredentials($user, ['password' => $data['password']])) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages(['email' => 'Akun Anda sedang ditangguhkan. Hubungi admin.']);
        }

        if (! $user->isEmailVerified()) {
            $request->session()->put('verification_user_id', $user->getAuthIdentifier());

            return redirect()->route('verification.notice')
                ->with('warning', 'Email Anda belum diverifikasi. Masukkan kode yang dikirim ke email universitas.');
        }

        if (! $user->isStaff()) {
            $this->ensureStudentMayLogin($user);
        }

        RateLimiter::clear($key);

        $guard->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('profile.show'));
    }

    /** SRS-003: logout. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout(); // juga memutar ulang remember token

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar.');
    }

    private function ensureStudentMayLogin(FirebaseUser $user): void
    {
        $status = $user->status_verifikasi ?? 'unverified';

        if ($status === 'verified') {
            return;
        }

        if ($status === 'rejected') {
            throw ValidationException::withMessages([
                'email' => 'Status kemahasiswaan Anda ditolak. Hubungi admin kampus Anda.',
            ]);
        }

        if (! config('campusmarket.student_verification.allow_login_while_pending')) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda masih menunggu konfirmasi status mahasiswa oleh Admin Universitas.',
            ]);
        }
    }
}
