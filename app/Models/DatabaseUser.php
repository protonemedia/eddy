<?php

namespace App\Models;

use App\Events\DatabaseUserDeleted;
use App\Events\DatabaseUserUpdated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Server $server
 * @property Collection<int, Database> $databases
 */
class DatabaseUser extends Model
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
        'updated' => DatabaseUserUpdated::class,
    ];

    protected static function booted()
    {
        static::deleted(function ($database) {
            event(new DatabaseUserDeleted($database->id, $database->server->team_id));
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(Database::class);
    }
}
