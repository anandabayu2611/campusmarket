<?php

namespace App\Repositories;

use Carbon\Carbon;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Laravel\Firebase\Facades\Firebase;

abstract class FirestoreRepository
{
    abstract protected function collectionName(): string;

    protected function db(): FirestoreClient
    {
        return Firebase::firestore()->database();
    }

    protected function collection(): CollectionReference
    {
        return $this->db()->collection($this->collectionName());
    }

    protected function now(): Timestamp
    {
        return new Timestamp(new \DateTimeImmutable());
    }

    /** Snapshot -> array biasa (Timestamp menjadi Carbon) + field `id`. */
    protected function hydrate(DocumentSnapshot $snapshot): ?array
    {
        if (! $snapshot->exists()) {
            return null;
        }

        return ['id' => $snapshot->id()] + $this->convert($snapshot->data());
    }

    private function convert(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Timestamp) {
                $data[$key] = Carbon::instance($value->get());
            } elseif (is_array($value)) {
                $data[$key] = $this->convert($value);
            }
        }

        return $data;
    }
}
