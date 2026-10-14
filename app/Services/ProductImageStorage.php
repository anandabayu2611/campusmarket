<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Foto produk disimpan di disk lokal "public" (storage/app/public/products/<id>/...),
 * tanpa Firebase Storage. Agar file bisa diakses dari browser, jalankan sekali:
 *     php artisan storage:link
 *
 * Di Firestore hanya disimpan path & URL relatif ("/storage/..."), sehingga
 * tetap valid walaupun APP_URL / domain berubah.
 */
class ProductImageStorage
{
    private const DISK = 'public';

    /** @return array{url:string, path:string} */
    public function store(string $productId, UploadedFile $file): array
    {
        $path = $file->storeAs(
            'products/' . $productId,
            Str::uuid() . '.' . $file->extension(),
            self::DISK
        );

        if ($path === false) {
            throw new \RuntimeException('Foto gagal disimpan ke disk lokal.');
        }

        return [
            'url'  => '/storage/' . $path,
            'path' => $path,
        ];
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($path);
        } catch (\Throwable $e) {
            report($e); // gagal hapus file lama tidak boleh menggagalkan proses utama
        }
    }
}
