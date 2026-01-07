<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $expiresHuman,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Code de connexion P'AS'SION BDS",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login_code',
        );
    }
}
