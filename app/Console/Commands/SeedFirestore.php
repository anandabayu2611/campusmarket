<?php

namespace App\Console\Commands;

use App\Repositories\StudentRecordRepository;
use App\Repositories\UniversityRepository;
use Illuminate\Console\Command;

class SeedFirestore extends Command
{
    protected $signature = 'campus:seed';
    protected $description = 'Isi Firestore dengan data universitas & contoh database mahasiswa (untuk development)';

    public function handle(UniversityRepository $universities, StudentRecordRepository $records): int
    {
        // CONTOH DATA — email_domain mencakup subdomain (mis. undip.ac.id menerima students.undip.ac.id).
        // Sesuaikan dengan domain email resmi tiap kampus.
        $list = [
            'undip' => ['Universitas Diponegoro', 'undip.ac.id', 'Semarang'],
            'ui'    => ['Universitas Indonesia', 'ui.ac.id', 'Depok'],
            'ugm'   => ['Universitas Gadjah Mada', 'ugm.ac.id', 'Yogyakarta'],
            'itb'   => ['Institut Teknologi Bandung', 'itb.ac.id', 'Bandung'],
            'unnes' => ['Universitas Negeri Semarang', 'unnes.ac.id', 'Semarang'],
        ];

        foreach ($list as $id => [$name, $domain, $city]) {
            $universities->save($id, ['name' => $name, 'email_domain' => $domain, 'city' => $city]);
            $this->line("universitas: {$name} (@{$domain})");
        }

        // CONTOH database mahasiswa universitas (data fiktif).
        $records->save('budi.santoso@students.undip.ac.id', [
            'university_id' => 'undip', 'name' => 'Budi Santoso', 'major' => 'Informatika', 'status' => 'active',
        ]);
        $records->save('siti.aminah@students.undip.ac.id', [
            'university_id' => 'undip', 'name' => 'Siti Aminah', 'major' => 'Sistem Informasi', 'status' => 'active',
        ]);
        $records->save('andi.wijaya@students.undip.ac.id', [
            'university_id' => 'undip', 'name' => 'Andi Wijaya', 'major' => 'Informatika', 'status' => 'graduated',
        ]);
        $this->line('student_records contoh: 3 data (undip)');

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
