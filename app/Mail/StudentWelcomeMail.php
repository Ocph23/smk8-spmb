<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun SPMB SMKN 8 Berhasil Dibuat — Segera Lengkapi Pendaftaran',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
