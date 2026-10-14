<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verifikasi email universitas (SRS-001, SRS-002)
    |--------------------------------------------------------------------------
    */
    'email_verification' => [
        'code_length'      => 6,
        'ttl_minutes'      => 15,   // masa berlaku kode
        'max_attempts'     => 5,    // salah input maksimal sebelum kode dibatalkan
        'resend_cooldown'  => 60,   // detik jeda minta kirim ulang
    ],

    /*
    |--------------------------------------------------------------------------
    | Verifikasi status kemahasiswaan
    |--------------------------------------------------------------------------
    | auto_approve_on_record_match : true  -> jika NIM + nama cocok dengan
    |                                         collection `student_records`, akun
    |                                         langsung "verified".
    |                                false -> semua pendaftar menunggu konfirmasi
    |                                         Admin Universitas.
    | allow_login_while_pending    : false -> hanya mahasiswa "verified" yang
    |                                         boleh login (sesuai Scenario 4).
    */
    'student_verification' => [
        'auto_approve_on_record_match' => true,
        'allow_login_while_pending'    => true,
    ],

    'roles' => ['student', 'admin', 'moderator', 'university_admin'],
];