<?php

namespace App\Models;

use Illuminate\Auth\GenericUser;

/**
 * Representasi user yang disimpan di Firestore (collection `users`).
 * Menurunkan GenericUser (Laravel 11+) agar kompatibel dengan guard session
 * termasuk "Remember Me", tanpa Eloquent.
 */
class FirebaseUser extends GenericUser
{
    public const STAFF_ROLES = ['admin', 'moderator', 'university_admin'];

    public function isStaff(): bool
    {
        return in_array($this->attributes['role'] ?? 'student', self::STAFF_ROLES, true);
    }

    public function isEmailVerified(): bool
    {
        return ! empty($this->attributes['email_verified_at']);
    }

    public function isStudentVerified(): bool
    {
        return ($this->attributes['status_verifikasi'] ?? null) === 'verified';
    }

    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? 'active') === 'active';
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
