<?php

namespace App\Repositories;

use Google\Cloud\Core\Timestamp;

/**
 * Collection `university_verifications` (ERD: UNIVERSITY_VERIFICATIONS), doc ID = user id:
 *   user_id, university_email, verification_code (HASH, bukan kode asli), verified_at, status
 *   + note, expires_at, attempts, last_sent_at (operasional)
 *
 * status: pending_email -> (kode benar) -> verified | pending (menunggu Admin Universitas) | rejected
 */
class UniversityVerificationRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'university_verifications';
    }

    public function find(string $userId): ?array
    {
        return $this->hydrate($this->collection()->document($userId)->snapshot());
    }

    /** Buat/ganti kode verifikasi (registrasi & kirim ulang). */
    public function start(string $userId, string $universityEmail, string $codeHash, \DateTimeInterface $expiresAt): void
    {
        $this->collection()->document($userId)->set([
            'user_id'           => $userId,
            'university_email'  => $universityEmail,
            'verification_code' => $codeHash,
            'status'            => 'pending_email',
            'verified_at'       => null,
            'note'              => null,
            'expires_at'        => new Timestamp($expiresAt),
            'attempts'          => 0,
            'last_sent_at'      => $this->now(),
        ]);
    }

    public function setAttempts(string $userId, int $attempts): void
    {
        $this->collection()->document($userId)->set(['attempts' => $attempts], ['merge' => true]);
    }

    /** Tetapkan hasil akhir; kode dihapus agar tidak bisa dipakai ulang. */
    public function resolve(string $userId, string $email, string $status, ?string $note, ?Timestamp $verifiedAt): void
    {
        $this->collection()->document($userId)->set([
            'user_id'           => $userId,
            'university_email'  => $email,
            'status'            => $status,
            'note'              => $note,
            'verified_at'       => $verifiedAt,
            'verification_code' => null,
            'expires_at'        => null,
        ], ['merge' => true]);
    }
}
