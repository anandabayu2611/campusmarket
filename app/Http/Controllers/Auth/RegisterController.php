<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UniversityRepository;
use App\Services\EmailVerificationService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(UniversityRepository $universities): View
    {
        return view('auth.register', ['universities' => $universities->all()]);
    }

    public function store(
        Request $request,
        RegistrationService $registration,
        EmailVerificationService $verification,
    ): RedirectResponse {
        $input = $request->validate([
            'name'          => ['required', 'string', 'min:3', 'max:100'],
            'university_id' => ['required', 'string', 'max:100'],
            'major'         => ['required', 'string', 'max:100'],
            'email'         => ['required', 'string', 'email:rfc', 'max:150'],
            'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms'         => ['accepted'],
        ], [
            'terms.accepted' => 'Anda harus menyetujui syarat penggunaan.',
        ]);

        $user = $registration->register($input);

        $request->session()->put('verification_user_id', $user['id']);

        try {
            $verification->send($user);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('verification.notice')
                ->with('warning', 'Akun dibuat, tetapi email gagal terkirim. Tekan "Kirim ulang kode".');
        }

        return redirect()->route('verification.notice')
            ->with('status', 'Kode verifikasi telah dikirim ke email universitas Anda.');
    }
}
