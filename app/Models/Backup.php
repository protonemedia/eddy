<?php

namespace App\Models;

use App\Events\BackupDeleted;
use App\Events\BackupUpdated;
use App\Tasks\RunBackupJob;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property string|null $frequency
 * @property string|null $custom_expression
 * @property EloquentCollection<int, Database> $databases
 * @property Disk $disk
 * @property Server $server
 * @property User $user
 */
class Backup extends Model
{
    const TIMESTAMP_FORMAT = 'Y-m-d-H-i-s';

    use HasUlids;
    use HasFactory;
    use InstallsAsynchronously;

    protected $fillable = [
        'name',
        'cron_expression',
        'disk_id',
        'include_files',
        'exclude_files',
        'retention',
        'notification_email',
        'notification_on_failure',
        'notification_on_success',
    ];

    protected $casts = [
        'include_files' => AsArrayObject::class,
        'exclude_files' => AsArrayObject::class,
        'retention' => 'integer',
        'installed_at' => 'datetime',
        'installation_failed_at' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
        'notification_on_failure' => 'boolean',
        'notification_on_success' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'saved' => BackupUpdated::class,
    ];

    protected static function booted()
    {
        static::creating(function (Backup $backup) {
            $backup->include_files ??= [];
            $backup->exclude_files ??= [];
            $backup->dispatch_token = Str::random(32);
        });

        static::deleted(function ($backup) {
            event(new BackupDeleted($backup->id, $backup->server->team_id));
        });
    }

    public function getSizeInMbAttribute(): int
    {
        $sizeInBytes = $this->jobs()->sum('size');

        return intval(round($sizeInBytes / 1024 / 1024));
    }

    public function getCronIntervalInSeconds(): int
    {
        $dates = (new CronExpression($this->cron_expression))->getMultipleRunDates(2);

        return Carbon::parse($dates[0])->diffInSeconds($dates[1]);
    }

    public function getNextRunAttribute(): Carbon
    {
        $nextRun = (new CronExpression($this->cron_expression))->getNextRunDate(now());

        return new Carbon($nextRun);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function disk(): BelongsTo
    {
        return $this->belongsTo(Disk::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(BackupJob::class);
    }

    public function latestJob(): HasOne
    {
        return $this->hasOne(BackupJob::class)->latestOfMany('created_at');
    }

    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(Database::class);
    }

    /**
     * Creates a new backup job for this backup with all databases in pending state.
     */
    public function createJob(): BackupJob
    {
        /** @var BackupJob|null */
        $pendingOrRunningJob = $this->jobs()->whereIn('status', [
            BackupJobStatus::Pending,
            BackupJobStatus::Running,
        ])->first();

        if ($pendingOrRunningJob && ! $pendingOrRunningJob->hasTimeouted()) {
            throw new CouldNotCreateBackupJobException($this);
        }

        /** @var BackupJob */
        return $this->jobs()->create([
            'disk_id' => $this->disk_id,
            'status' => BackupJobStatus::Pending,
        ]);
    }

    /**
     * Creates a new backup job and dispatches it to the server.
     */
    public function createAndDispatchJob(): BackupJob
    {
        return tap($this->createJob(), function (BackupJob $backupJob) {
            $this->server->runTask(new RunBackupJob($backupJob))
                ->asUser()
                ->inBackground()
                ->dispatch();
        });
    }

    /**
     * Find old backup jobs that can be deleted, delete them from the database, and return their filenames.
     */
    public function cleanupAndFindDeletableBackups(): array
    {
        if ($this->retention < 1) {
            return [];
        }

        $oldestBackupToKeep = $this->jobs()
            ->latest()
            ->finished()
            ->skip($this->retention - 1)
            ->first();

        if (! $oldestBackupToKeep) {
            return [];
        }

        $backupJobsToDelete = $this->jobs()
            ->where('created_at', '<', $oldestBackupToKeep->created_at)
            ->get()
            ->each->setRelation('backup', $this);

        $filenames = $backupJobsToDelete->map->generateOutputFilename()->all();

        $this->jobs()
            ->whereKey($backupJobsToDelete->modelKeys())
            ->delete();

        return $filenames;
    }

    /**
     * Returns the path to the cron file on the server.
     */
    public function cronPath(): string
    {
        return "/etc/cron.d/backup-{$this->id}";
    }

    /**
     * Generates the cron command to run this backup.
     */
    public function cronCommand(): string
    {
        $dispatchUrl = URL::relativeSignedRoute('backup-job.store', [$this, $this->dispatch_token]);

        return "(curl -X POST --max-time 15 {$dispatchUrl})";
    }
}
