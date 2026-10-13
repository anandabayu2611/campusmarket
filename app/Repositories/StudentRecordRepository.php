<?php

namespace App\Repositories;

/**
 * Collection `student_records` = cermin "database mahasiswa" universitas
 * (hasil impor / integrasi sistem akademik, lihat sequence diagram Verifikasi Mahasiswa).
 * Doc ID diturunkan dari email universitas:
 *   { university_email, university_id, name, major, status: active|inactive|graduated }
 */
class StudentRecordRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'student_records';
    }

    public static function idForEmail(string $email): string
    {
        return 'rec_' . substr(hash('sha256', mb_strtolower(trim($email))), 0, 32);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->hydrate($this->collection()->document(self::idForEmail($email))->snapshot());
    }

    public function save(string $email, array $data): void
    {
        $this->collection()->document(self::idForEmail($email))->set(
            $data + ['university_email' => mb_strtolower(trim($email))],
            ['merge' => true]
        );
    }
}
