<?php

namespace App\Models;

use App\Notifications\DeploymentFailed;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property Site $site
 * @property Task $task
 * @property User|null $user
 */
class Deployment extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'status' => DeploymentStatus::class,
        'user_notified_at' => 'datetime',
    ];

    protected $guarded = [];

    /**
     * Returns the first seven characters of the git commit hash.
     */
    public function getShortGitHashAttribute(): string
    {
        return Str::substr($this->git_hash, 0, 7);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Notifies the user that the deployment failed.
     */
    public function notifyUserAboutFailedDeployment(): void
    {
        if ($this->user_notified_at) {
            return;
        }

        $notifiable = $this->user ?: $this->site->deployNotifiable();
        $notifiable?->notify(new DeploymentFailed($this));

        $this->forceFill([
            'user_notified_at' => now(),
        ])->save();
    }
}
