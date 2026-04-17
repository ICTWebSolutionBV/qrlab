<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path: string, name: string, mime: string}>  $attachments
     */
    public function __construct(
        public string $message,
        public ?string $submitterName,
        public ?string $submitterEmail,
        public ?string $userName,
        public ?string $userEmail,
        public ?string $userRole,
        public ?string $pageUrl,
        public array $attachmentFiles = [],
    ) {}

    public function envelope(): Envelope
    {
        $subject = 'Feedback from ' . ($this->submitterName ?: $this->userName ?: $this->userEmail ?: 'QR Lab user');

        $replyTo = [];
        if ($this->submitterEmail) {
            $replyTo[] = new Address($this->submitterEmail, $this->submitterName ?: $this->submitterEmail);
        } elseif ($this->userEmail) {
            $replyTo[] = new Address($this->userEmail, $this->userName ?: $this->userEmail);
        }

        return new Envelope(
            subject: $subject,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback',
            with: [
                'messageBody' => $this->message,
                'submitterName' => $this->submitterName,
                'submitterEmail' => $this->submitterEmail,
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'userRole' => $this->userRole,
                'pageUrl' => $this->pageUrl,
                'appName' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return array_map(
            fn ($a) => Attachment::fromPath($a['path'])
                ->as($a['name'])
                ->withMime($a['mime']),
            $this->attachmentFiles,
        );
    }
}
