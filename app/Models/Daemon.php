<?php

namespace App\Models;

use App\Events\DaemonDeleted;
use App\Events\DaemonUpdated;
use App\Signal;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Server $server
 */
class Daemon extends Model
{
    use HasFactory;
    use HasUlids;
    use InstallsAsynchronously;

    protected $casts = [
        'command' => 'encrypted',
        'processes' => 'integer',
        'stop_wait_seconds' => 'integer',
        'stop_signal' => Signal::class,
        'installed_at' => 'datetime',
        'installation_failed_at' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
    ];

    protected $fillable = [
        'command',
        'directory',
        'user',
        'processes',
        'stop_wait_seconds',
        'stop_signal',
    ];

    protected $dispatchesEvents = [
        'updated' => DaemonUpdated::class,
    ];

    protected static function booted()
    {
        static::deleted(function ($daemon) {
            event(new DaemonDeleted($daemon->id, $daemon->server->team_id));
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Returns the path to the daemon file on the server.
     */
    public function path(): string
    {
        return "/etc/supervisor/conf.d/daemon-{$this->id}.conf";
    }

    /**
     * Returns the path to the daemon's error log file on the server.
     */
    public function errorLogPath(): string
    {
        return $this->user === 'root'
            ? "/root/{$this->server->working_directory}/daemon-{$this->id}.err"
            : "/home/{$this->user}/{$this->server->working_directory}/daemon-{$this->id}.err";
    }

    /**
     * Returns the path to the daemon's output log file on the server.
     */
    public function outputLogPath(): string
    {
        return $this->user === 'root'
            ? "/root/{$this->server->working_directory}/daemon-{$this->id}.log"
            : "/home/{$this->user}/{$this->server->working_directory}/daemon-{$this->id}.log";
    }
}
