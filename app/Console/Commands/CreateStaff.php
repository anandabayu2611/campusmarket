<?php

namespace App\Console\Commands;

use App\Exceptions\DuplicateAccountException;
use App\Repositories\UserRepository;
use Google\Cloud\Core\Timestamp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateStaff extends Command
{
    protected $signature = 'campus:create-staff {email} {name} {role=admin : admin|moderator|university_admin} {--university= : ID universitas (untuk university_admin)}';
    protected $description = 'Buat akun Admin / Moderator Konten / Admin Universitas (bisa login — UC-02)';

    public function handle(UserRepository $users): int
    {
        $role = $this->argument('role');
        if (! in_array($role, ['admin', 'moderator', 'university_admin'], true)) {
            $this->error('Role harus: admin, moderator, atau university_admin.');

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (min. 8 karakter)');
        if (strlen((string) $password) < 8) {
            $this->error('Kata sandi minimal 8 karakter.');

            return self::FAILURE;
        }

        try {
            $users->createStaff([
                'name'                => $this->argument('name'),
                'email'               => mb_strtolower($this->argument('email')),
                'password'            => Hash::make($password),
                'role'                => $role,
                'university_id'       => $this->option('university'),
                'email_verified_at'   => new Timestamp(new \DateTimeImmutable()),
                'status_verifikasi'   => 'verified',
                'status'              => 'active',
                'remember_token'      => null,
            ]);
        } catch (DuplicateAccountException) {
            $this->error('Email tersebut sudah terdaftar.');

            return self::FAILURE;
        }

        $this->info("Akun {$role} dibuat: " . $this->argument('email'));

        return self::SUCCESS;
    }
}
