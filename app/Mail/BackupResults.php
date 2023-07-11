<?php

namespace App\Mail;

use App\Models\BackupJob;
use App\Models\BackupJobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupResults extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public BackupJob $backupJob)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->backupJob->status === BackupJobStatus::Finished
                ? __('Backup Finished: :name', ['name' => $this->backupJob->backup->name])
                : __('Backup Failed: :name', ['name' => $this->backupJob->backup->name])
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.backup-results',
            with: [
                'isFinished' => $this->backupJob->status === BackupJobStatus::Finished,
                'backup' => $this->backupJob->backup,
                'disk' => $this->backupJob->backup->disk,
                'server' => $this->backupJob->backup->server,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
