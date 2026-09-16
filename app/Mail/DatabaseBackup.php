<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackup extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $files,
        public string $timestamp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Weekly Finance Backup — {$this->timestamp}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backup',
        );
    }

    public function attachments(): array
    {
        return array_map(function ($path) {
            $basename = basename($path);
            return \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                ->as($basename)
                ->withMime('text/csv');
        }, $this->files);
    }
}
