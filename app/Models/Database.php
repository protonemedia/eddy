<?php

namespace App\Models;

use App\Events\DatabaseDeleted;
use App\Events\DatabaseUpdated;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Server $server
 */
class Database extends Model
{
    use HasFactory;
    use HasUlids;
    use InstallsAsynchronously;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'installation_failed_at' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'updated' => DatabaseUpdated::class,
    ];

    protected static function booted()
    {
        static::deleted(function ($database) {
            event(new DatabaseDeleted($database->id, $database->server->team_id));
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(DatabaseUser::class);
    }
}
