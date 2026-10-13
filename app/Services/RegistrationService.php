<?php

namespace App\Services;

use App\Exceptions\DuplicateAccountException;
use App\Repositories\UniversityRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UniversityRepository $universities,
    ) {
    }

    /**
     * Daftarkan mahasiswa (SRS-001) setelah memvalidasi domain email universitas (SRS-002).
     * Mengembalikan data user yang tersimpan (array, termasuk `id`).
     *
     * @throws ValidationException
     */
    public function register(array $input): array
    {
        $email = mb_strtolower(trim($input['email']));

        $university = $this->universities->find($input['university_id']);
        if (! $university) {
            throw ValidationException::withMessages(['university_id' => 'Universitas tidak ditemukan.']);
        }

        // Sequence diagram: "Validasi format & domain email" -> domain tidak valid: tolak registrasi.
        $byDomain = $this->universities->findByEmail($email);
        if (! $byDomain || $byDomain['id'] !== $university['id']) {
            throw ValidationException::withMessages([
                'email' => "Gunakan email resmi {$university['name']} (berakhiran @{$university['email_domain']}).",
            ]);
        }

        $data = [
            'university_id'     => $university['id'],
            'name'              => trim($input['name']),
            'email'             => $email,
            'password'          => Hash::make($input['password']),
            'major'             => trim($input['major']),
            'role'              => 'student',
            'status_verifikasi' => 'unverified',
            'phone_number'      => null,
            'campus_location'   => null,
            'avatar_url'        => null,
            'avatar_path'       => null,
            'email_verified_at' => null,
            'status'            => 'active',
            'remember_token'    => null,
        ];

        try {
            $id = $this->users->createStudent($data);
        } catch (DuplicateAccountException) {
            throw ValidationException::withMessages(['email' => 'Email ini sudah terdaftar. Silakan login.']);
        }

        return ['id' => $id] + $data;
    }
}
