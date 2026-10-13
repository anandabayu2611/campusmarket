<?php

namespace App\Auth;

use App\Models\FirebaseUser;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

/**
 * UserProvider kustom: Laravel Auth (session + Remember Me) dengan Firestore
 * sebagai penyimpanan user.
 */
class FirestoreUserProvider implements UserProvider
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $data = $this->users->find((string) $identifier);

        return $data ? new FirebaseUser($data) : null;
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        $user = $this->retrieveById($identifier);

        if (! $user) {
            return null;
        }

        $stored = $user->getRememberToken();

        return $stored && $token && hash_equals($stored, $token) ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
        $user->setRememberToken($token);
        $this->users->update((string) $user->getAuthIdentifier(), ['remember_token' => $token]);
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        if (empty($credentials['email'])) {
            return null;
        }

        $data = $this->users->findByEmail($credentials['email']);

        return $data ? new FirebaseUser($data) : null;
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        return isset($credentials['password'])
            && Hash::check($credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
        if (! $force && ! Hash::needsRehash($user->getAuthPassword())) {
            return;
        }

        $this->users->update((string) $user->getAuthIdentifier(), [
            'password' => Hash::make($credentials['password']),
        ]);
    }
}
