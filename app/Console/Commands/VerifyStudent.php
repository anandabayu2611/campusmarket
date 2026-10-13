<?php

namespace App\Console\Commands;

use App\Services\StudentVerificationService;
use Illuminate\Console\Command;

class VerifyStudent extends Command
{
    protected $signature = 'campus:verify-student {email} {--reject : Tolak, bukan setujui} {--note= : Catatan untuk mahasiswa}';
    protected $description = 'Simulasi aksi Admin Universitas: setujui/tolak status kemahasiswaan (Scenario 4, langkah 7)';

    public function handle(StudentVerificationService $service): int
    {
        $email = $this->argument('email');
        $note  = $this->option('note') ?: null;

        $ok = $this->option('reject') ? $service->reject($email, $note) : $service->approve($email, $note);

        if (! $ok) {
            $this->error('Mahasiswa dengan email tersebut tidak ditemukan.');

            return self::FAILURE;
        }

        $this->info($this->option('reject') ? 'Mahasiswa ditolak.' : 'Mahasiswa diverifikasi.');

        return self::SUCCESS;
    }
}
