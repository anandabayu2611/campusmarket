<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;

/**
 * Foto profil di Firebase Storage. URL memakai download token Firebase
 * (permanen, tidak perlu ACL publik sehingga cocok untuk bucket "uniform access").
 */
class AvatarStorage
{
    /** @return array{url:string, path:string} */
    public function store(string $userId, UploadedFile $file): array
    {
        $bucket = Firebase::storage()->getBucket();
        $path   = sprintf('avatars/%s/%s.%s', $userId, Str::uuid(), $file->extension());
        $token  = (string) Str::uuid();

        $stream = fopen($file->getRealPath(), 'r');
        try {
            $bucket->upload($stream, [
                'name'     => $path,
                'metadata' => [
                    'contentType' => $file->getMimeType(),
                    'metadata'    => ['firebaseStorageDownloadTokens' => $token],
                ],
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $url = sprintf(
            'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media&token=%s',
            $bucket->name(),
            rawurlencode($path),
            $token
        );

        return ['url' => $url, 'path' => $path];
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Firebase::storage()->getBucket()->object($path)->delete();
        } catch (\Throwable $e) {
            report($e); // gagal hapus file lama tidak boleh menggagalkan update profil
        }
    }
}
