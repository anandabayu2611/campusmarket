<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Services\EmailVerificationService;
use App\Support\EmailDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EmailVerificationService $verification,
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify-email', [
            'maskedEmail' => EmailDomain::mask($user['email']),
            'cooldown'    => $this->verification->cooldownRemaining($user['id']),
            'ttl'         => config('campusmarket.email_verification.ttl_minutes'),
            'length'      => config('campusmarket.email_verification.code_length'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $length = (int) config('campusmarket.email_verification.code_length');
        $data   = $request->validate([
            'code' => ['required', 'digits:' . $length],
        ], ['code.digits' => "Kode terdiri dari {$length} angka."]);

        $status = $this->verification->verify($user, $data['code']);

        $request->session()->forget('verification_user_id');

        $message = match ($status) {
            'verified' => 'Email terverifikasi dan status mahasiswa dikonfirmasi. Silakan login.',
            'rejected' => 'Email terverifikasi, tetapi status kemahasiswaan Anda ditolak. Hubungi admin kampus.',
            default    => 'Email terverifikasi. Akun Anda menunggu konfirmasi Admin Universitas; kami akan memberi tahu setelah selesai.',
        };

        return redirect()->route('login')->with($status === 'verified' ? 'status' : 'warning', $message);
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $this->verification->send($user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning', 'Email gagal terkirim. Coba lagi beberapa saat.');
        }

        return back()->with('status', 'Kode baru telah dikirim.');
    }

    /** User yang sedang menunggu verifikasi email (disimpan di session saat register/login). */
    private function pendingUser(Request $request): ?array
    {
        $id = $request->session()->get('verification_user_id');
        if (! $id) {
            return null;
        }

        $user = $this->users->find($id);
        if (! $user || ! empty($user['email_verified_at'])) {
            $request->session()->forget('verification_user_id');

            return null;
        }

        return $user;
    }
}
