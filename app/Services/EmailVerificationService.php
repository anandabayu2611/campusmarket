<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Repositories\UniversityVerificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UniversityVerificationRepository $verifications,
        private readonly StudentVerificationService $students,
    ) {
    }

    /** Detik tersisa sebelum kode boleh dikirim ulang (0 = boleh). */
    public function cooldownRemaining(string $userId): int
    {
        $record = $this->verifications->find($userId);
        if (! $record || empty($record['last_sent_at'])) {
            return 0;
        }

        $elapsed = now()->timestamp - $record['last_sent_at']->timestamp;

        return max(0, (int) config('campusmarket.email_verification.resend_cooldown') - $elapsed);
    }

    /** Buat kode baru, simpan hash-nya, kirim ke email universitas. */
    public function send(array $user): void
    {
        $wait = $this->cooldownRemaining($user['id']);
        if ($wait > 0) {
            throw ValidationException::withMessages([
                'code' => "Tunggu {$wait} detik sebelum meminta kode baru.",
            ]);
        }

        $cfg  = config('campusmarket.email_verification');
        $len  = (int) $cfg['code_length'];
        $code = str_pad((string) random_int(0, (10 ** $len) - 1), $len, '0', STR_PAD_LEFT);

        $this->verifications->start(
            $user['id'],
            $user['email'],
            $this->hash($user['id'], $code),
            now()->addMinutes((int) $cfg['ttl_minutes'])->toDateTimeImmutable()
        );

        Mail::to($user['email'])->send(new VerificationCodeMail($user['name'], $code, (int) $cfg['ttl_minutes']));
    }

    /**
     * Periksa kode. Jika benar: tandai email terverifikasi lalu cocokkan data mahasiswa.
     *
     * @return string status mahasiswa: verified | pending | rejected
     * @throws ValidationException
     */
    public function verify(array $user, string $code): string
    {
        $max    = (int) config('campusmarket.email_verification.max_attempts');
        $record = $this->verifications->find($user['id']);

        if (! $record || empty($record['verification_code']) || ($record['status'] ?? '') !== 'pending_email') {
            $this->fail('Kode tidak ditemukan. Silakan minta kode baru.');
        }
        if ($record['expires_at']->isPast()) {
            $this->fail('Kode sudah kedaluwarsa. Silakan minta kode baru.');
        }
        if (($record['attempts'] ?? 0) >= $max) {
            $this->fail('Terlalu banyak percobaan salah. Silakan minta kode baru.');
        }

        if (! hash_equals($record['verification_code'], $this->hash($user['id'], $code))) {
            $attempts = ($record['attempts'] ?? 0) + 1;
            $this->verifications->setAttempts($user['id'], $attempts);

            $left = max(0, $max - $attempts);
            $this->fail("Kode salah. Sisa percobaan: {$left}.");
        }

        $this->users->markEmailVerified($user['id']);

        return $this->students->evaluate($user);
    }

    private function hash(string $userId, string $code): string
    {
        return hash_hmac('sha256', $userId . ':' . $code, (string) config('app.key'));
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['code' => $message]);
    }
}
