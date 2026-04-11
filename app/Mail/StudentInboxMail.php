<?php

namespace App\Mail;

use App\Models\Inbox;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentInboxMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inbox $inboxMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SPMB SMKN 8] ' . $this->inboxMessage->subject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student-inbox');
    }

    public function attachments(): array
    {
        return [];
    }
}
