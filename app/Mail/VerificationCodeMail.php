<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerificationCodeMail extends Mailable
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly int $ttlMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode verifikasi CampusMarket: ' . $this->code);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verification-code');
    }
}
