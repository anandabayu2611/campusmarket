<?php

namespace App\Repositories;

use App\Support\EmailDomain;

/**
 * Collection `universities` (ERD: UNIVERSITIES) — id, name, email_domain, city
 * `email_domain` mencakup subdomain-nya: "undip.ac.id" menerima juga "students.undip.ac.id".
 */
class UniversityRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'universities';
    }

    /** Semua universitas, urut nama (jumlahnya kecil -> urut di PHP, tanpa index). */
    public function all(): array
    {
        $result = [];
        foreach ($this->collection()->documents() as $snap) {
            if ($row = $this->hydrate($snap)) {
                $result[] = $row;
            }
        }
        usort($result, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $result;
    }

    public function find(string $id): ?array
    {
        return $this->hydrate($this->collection()->document($id)->snapshot());
    }

    /** Cari universitas dari domain email, termasuk subdomain (SRS-002; class University::verifyDomain()). */
    public function findByEmail(string $email): ?array
    {
        foreach (EmailDomain::candidates($email) as $domain) {
            $docs = $this->collection()->where('email_domain', '=', $domain)->limit(1)->documents();

            foreach ($docs as $snap) {
                if ($row = $this->hydrate($snap)) {
                    return $row;
                }
            }
        }

        return null;
    }

    public function save(string $id, array $data): void
    {
        $this->collection()->document($id)->set($data, ['merge' => true]);
    }
}
