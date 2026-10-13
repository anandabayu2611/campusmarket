<?php

namespace App\Services;

use App\Repositories\StudentRecordRepository;
use App\Repositories\UniversityVerificationRepository;
use App\Repositories\UserRepository;
use App\Support\EmailDomain;
use Google\Cloud\Core\Timestamp;

/**
 * Verifikasi status kemahasiswaan (SRS-001, SRS-002) — sesuai sequence diagram "Verifikasi Mahasiswa":
 *  - "Cocokkan data dengan database mahasiswa": email universitas + nama dicocokkan dengan `student_records`
 *  - "Konfirmasi status mahasiswa aktif" / "Tolak verifikasi": Admin Universitas (command campus:verify-student)
 */
class StudentVerificationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly StudentRecordRepository $records,
        private readonly UniversityVerificationRepository $verifications,
    ) {
    }

    /** Dipanggil setelah kode email benar. Mengembalikan: verified | pending | rejected */
    public function evaluate(array $user): string
    {
        $status = 'pending';
        $note   = 'Menunggu konfirmasi Admin Universitas.';

        if (config('campusmarket.student_verification.auto_approve_on_record_match')) {
            $record = $this->records->findByEmail($user['email']);

            if (! $record) {
                $note = 'Data belum ditemukan di database mahasiswa universitas; menunggu pemeriksaan Admin Universitas.';
            } elseif (($record['university_id'] ?? $user['university_id']) !== $user['university_id']) {
                $note = 'Universitas tidak sesuai dengan data mahasiswa; menunggu pemeriksaan Admin Universitas.';
            } elseif (($record['status'] ?? 'active') !== 'active') {
                $status = 'rejected';
                $note   = 'Status kemahasiswaan tidak aktif pada data universitas.';
            } elseif (EmailDomain::normalizeName($record['name'] ?? '') === EmailDomain::normalizeName($user['name'])) {
                $status = 'verified';
                $note   = 'Cocok dengan data mahasiswa universitas.';
            } else {
                $note = 'Nama tidak sama dengan data universitas; menunggu pemeriksaan Admin Universitas.';
            }
        }

        $this->apply($user, $status, $note);

        return $status;
    }

    /** Aksi Admin Universitas: konfirmasi mahasiswa aktif. */
    public function approve(string $email, ?string $note = null): bool
    {
        return $this->applyByEmail($email, 'verified', $note ?? 'Dikonfirmasi Admin Universitas.');
    }

    /** Aksi Admin Universitas: tolak verifikasi. */
    public function reject(string $email, ?string $note = null): bool
    {
        return $this->applyByEmail($email, 'rejected', $note ?? 'Ditolak Admin Universitas.');
    }

    private function applyByEmail(string $email, string $status, string $note): bool
    {
        $user = $this->users->findByEmail($email);
        if (! $user || ($user['role'] ?? 'student') !== 'student') {
            return false;
        }

        $this->apply($user, $status, $note);

        return true;
    }

    private function apply(array $user, string $status, string $note): void
    {
        $this->users->update($user['id'], ['status_verifikasi' => $status]);

        $this->verifications->resolve(
            $user['id'],
            $user['email'],
            $status,
            $note,
            $status === 'verified' ? new Timestamp(new \DateTimeImmutable()) : null
        );
    }
}
