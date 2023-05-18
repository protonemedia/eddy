<?php

namespace App\Models;

use App\Events\CronDeleted;
use App\Events\CronUpdated;
use Cron\CronExpression as BaseCronExpression;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $frequency
 * @property string|null $custom_expression
 * @property BaseCronExpression $expression
 * @property Server $server
 */
class Cron extends Model
{
    use HasFactory;
    use HasUlids;
    use InstallsAsynchronously;

    protected $casts = [
        'command' => 'encrypted',
        'installed_at' => 'datetime',
        'installation_failed_at' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
    ];

    protected $fillable = [
        'command',
        'user',
        'expression',
    ];

    protected $dispatchesEvents = [
        'updated' => CronUpdated::class,
    ];

    protected static function booted()
    {
        static::deleted(function ($cron) {
            event(new CronDeleted($cron->id, $cron->server->team_id));
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Returns the path to the cron file on the server.
     */
    public function path(): string
    {
        return "/etc/cron.d/cron-{$this->id}";
    }

    /**
     * Returns the path to the cron's log file on the server.
     */
    public function logPath(): string
    {
        return $this->user === 'root'
            ? "/root/{$this->server->working_directory}/cron-{$this->id}.log"
            : "/home/{$this->user}/{$this->server->working_directory}/cron-{$this->id}.log";
    }
}
