<?php

namespace App\Repositories;

use App\Exceptions\DuplicateAccountException;
use Google\Cloud\Firestore\Transaction;

/**
 * Collection `users` (ERD: USERS) —
 *   id, university_id, name, email, major, role, status_verifikasi, created_at
 *   + password, phone_number & campus_location (class Student), status, remember_token,
 *     email_verified_at, avatar_url, avatar_path
 */
class UserRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'users';
    }

    /**
     * ID dokumen diturunkan dari email. Firestore tidak punya UNIQUE constraint,
     * jadi ID deterministik ini menjamin satu email = satu akun (bebas race condition).
     * Konsekuensi: email tidak dapat diubah (memang identitas kampus).
     */
    public static function idForEmail(string $email): string
    {
        return 'usr_' . substr(hash('sha256', mb_strtolower(trim($email))), 0, 32);
    }

    public function find(string $id): ?array
    {
        return $this->hydrate($this->collection()->document($id)->snapshot());
    }

    public function findByEmail(string $email): ?array
    {
        return $this->find(self::idForEmail($email));
    }

    /**
     * Simpan akun mahasiswa secara atomik.
     *
     * Anti "email squatting": jika email sudah ada tetapi BELUM terverifikasi, pendaftaran baru
     * menimpa data lama (password, jurusan, dll). Dengan begitu pemilik email yang sebenarnya,
     * yang dibuktikan lewat kode verifikasi, selalu mendapat akun berisi data miliknya sendiri.
     *
     * @throws DuplicateAccountException
     */
    public function createStudent(array $data): string
    {
        $id  = self::idForEmail($data['email']);
        $ref = $this->collection()->document($id);
        $now = $this->now();

        $this->db()->runTransaction(function (Transaction $tx) use ($ref, $data, $now) {
            $snap = $tx->snapshot($ref);

            if ($snap->exists() && ! empty($snap->data()['email_verified_at'])) {
                throw new DuplicateAccountException('email');
            }

            $tx->set($ref, $data + ['created_at' => $now, 'updated_at' => $now]);
        });

        return $id;
    }

    /** Akun staf (admin/moderator/admin universitas). */
    public function createStaff(array $data): string
    {
        $id  = self::idForEmail($data['email']);
        $ref = $this->collection()->document($id);

        if ($ref->snapshot()->exists()) {
            throw new DuplicateAccountException('email');
        }

        $now = $this->now();
        $ref->create($data + ['created_at' => $now, 'updated_at' => $now]);

        return $id;
    }

    public function update(string $id, array $data): void
    {
        $this->collection()->document($id)->set(
            $data + ['updated_at' => $this->now()],
            ['merge' => true]
        );
    }

    public function markEmailVerified(string $id): void
    {
        $this->update($id, ['email_verified_at' => $this->now()]);
    }
}
