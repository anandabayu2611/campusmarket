<?php

namespace App\Repositories;

/**
 * Collection `reviews` (ERD: REVIEWS) — id, order_id, reviewer_id, reviewee_id, score, comment.
 * Diisi oleh fitur Rating & Review (SRS-021, sprint berikutnya). Profil hanya MEMBACA ringkasannya.
 *
 * CATATAN: ERD tidak memuat peran yang dinilai; agar rating "sebagai penjual/pembeli" (SRS-004)
 * bisa dipisah, setiap review perlu field `reviewee_role` ('seller' | 'buyer').
 */
class ReviewRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'reviews';
    }

    /** @return array{seller: array{average: float, count: int}, buyer: array{average: float, count: int}} */
    public function summaryFor(string $userId): array
    {
        $sum = ['seller' => [0, 0], 'buyer' => [0, 0]];

        foreach ($this->collection()->where('reviewee_id', '=', $userId)->documents() as $snap) {
            $row  = $snap->data();
            $role = $row['reviewee_role'] ?? null;

            if (isset($sum[$role]) && isset($row['score'])) {
                $sum[$role][0] += (float) $row['score'];
                $sum[$role][1]++;
            }
        }

        $out = [];
        foreach ($sum as $role => [$total, $count]) {
            $out[$role] = ['average' => $count ? round($total / $count, 1) : 0.0, 'count' => $count];
        }

        return $out;
    }
}
