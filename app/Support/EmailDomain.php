<?php

namespace App\Support;

class EmailDomain
{
    /** Ambil domain dari alamat email (huruf kecil), atau null jika tidak valid. */
    public static function fromEmail(string $email): ?string
    {
        $email = mb_strtolower(trim($email));
        $pos = strrpos($email, '@');

        if ($pos === false || $pos === strlen($email) - 1) {
            return null;
        }

        return substr($email, $pos + 1);
    }

    /**
     * Daftar domain kandidat dari yang paling spesifik ke yang lebih umum.
     * "mhs.fakultas.undip.ac.id" => [mhs.fakultas.undip.ac.id, fakultas.undip.ac.id, undip.ac.id]
     * Domain generik 2-label (ac.id, co.id, ...) tidak pernah dijadikan kandidat.
     */
    public static function candidates(string $email): array
    {
        $domain = self::fromEmail($email);
        if ($domain === null) {
            return [];
        }

        $labels = explode('.', $domain);
        $out = [];

        while (count($labels) >= 2) {
            $out[] = implode('.', $labels);
            array_shift($labels);
        }

        $generic = ['ac.id', 'co.id', 'go.id', 'or.id', 'sch.id', 'edu.au', 'ac.uk', 'edu'];

        return array_values(array_filter($out, fn (string $d) => ! in_array($d, $generic, true)));
    }

    public static function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));

        return trim(preg_replace('/[^a-z0-9]+/u', ' ', $name) ?? $name);
    }

    public static function mask(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible . str_repeat('*', max(1, mb_strlen($local) - 2)) . '@' . $domain;
    }
}
