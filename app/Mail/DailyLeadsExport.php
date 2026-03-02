<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DailyLeadsExport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $fileName,
        public array  $stats = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Today's New Leads Report — " . now()->setTimezone('Asia/Kolkata')->format('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-leads-export', with: [
            'stats'    => $this->stats,
            'fileName' => $this->fileName,
            'date'     => now()->setTimezone('Asia/Kolkata')->format('d M Y'),
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
