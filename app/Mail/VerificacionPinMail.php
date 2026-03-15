<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificacionPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $pin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu PIN de verificación - Biblioteca Digital',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verificacion-pin',
        );
    }
}
