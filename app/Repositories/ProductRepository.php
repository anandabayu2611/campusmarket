<?php

namespace App\Repositories;

use Illuminate\Support\Str;

class ProductRepository extends FirestoreRepository
{
    protected function collectionName(): string
    {
        return 'products';
    }

    public function all(): array
    {
        $result = [];

        foreach ($this->collection()->documents() as $snapshot) {
            if ($snapshot->exists()) {
                $result[] = $this->hydrate($snapshot);
            }
        }

        return $result;
    }

    public function find(string $id): ?array
    {
        return $this->hydrate(
            $this->collection()->document($id)->snapshot()
        );
    }

    public function create(array $data): string
    {
        $id = $data['id'] ?? (string) Str::uuid();

        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();

        $this->collection()->document($id)->set($data);

        return $id;
    }

    public function update(string $id, array $data): void
    {
        $data['updated_at'] = $this->now();

        $this->collection()
            ->document($id)
            ->set($data, ['merge' => true]);
    }

    public function delete(string $id): void
    {
        $this->collection()->document($id)->delete();
    }
}