<?php

namespace App\Models;

use App\Mail\BackupResults;
use App\UlidGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property Backup $backup
 * @property Disk $disk
 */
class BackupJob extends Model
{
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'status' => BackupJobStatus::class,
        'error' => 'encrypted',
    ];

    protected $touches = ['backup'];

    /**
     * Generate a new ULID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (new UlidGenerator)->generate();
    }

    public function getSizeInMbAttribute(): int
    {
        return intval(round($this->size / 1024 / 1024));
    }

    public function backup()
    {
        return $this->belongsTo(Backup::class);
    }

    public function disk()
    {
        return $this->belongsTo(Disk::class);
    }

    public function scopeFinished(Builder $query)
    {
        return $query->where($query->qualifyColumn('status'), BackupJobStatus::Finished);
    }

    /**
     * Returns a boolean whether the backup job has timed out.
     */
    public function hasTimeouted(): bool
    {
        if (in_array($this->status, [BackupJobStatus::Finished, BackupJobStatus::Failed])) {
            return false;
        }

        $maxExecutionTime = $this->backup->getCronIntervalInSeconds() * 5;

        $timeouted = $this->created_at->addSeconds($maxExecutionTime)->isPast();

        if ($timeouted) {
            $this->markAsFailed('The backup job has timed out.');
            $this->mailResultsIfUserShouldBeNotified();
        }

        return $timeouted;
    }

    public function markAsFailed(string $error, int $size = 0): void
    {
        $this->update([
            'status' => BackupJobStatus::Failed,
            'error' => $error,
            'size' => $size,
        ]);
    }

    public function markAsFinished(int $size = 0): void
    {
        $this->update([
            'status' => BackupJobStatus::Finished,
            'size' => $size,
        ]);
    }

    public function mailResultsIfUserShouldBeNotified(): void
    {
        if ($this->userShouldBeNotified()) {
            Mail::to($this->backup->notification_email)->queue(new BackupResults($this));
        }
    }

    /**
     * Returns a boolean whether a notification should be sent.
     */
    public function userShouldBeNotified(): bool
    {
        if (! $this->backup->notification_email) {
            return false;
        }

        if ($this->status === BackupJobStatus::Finished) {
            return $this->backup->notification_on_success;
        }

        return $this->backup->notification_on_failure;
    }

    /**
     * Generate the output filename for this database backup.
     */
    public function generateOutputFilename(): string
    {
        $name = Str::slug($this->backup->name);

        $timestamp = $this->created_at->format(Backup::TIMESTAMP_FORMAT);

        return "{$timestamp}-$name";
    }

    public function cliUrl(): string
    {
        return URL::relativeSignedRoute('backup-job.show', $this);
    }
}
