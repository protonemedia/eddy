<?php

namespace App\Models;

use App\Enum;
use App\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property User $user
 */
class Credentials extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'provider' => Provider::class,
        'credentials' => AsEncryptedArrayObject::class,
    ];

    protected $fillable = [
        'name', 'provider', 'credentials',
    ];

    protected static function booted()
    {
        static::creating(function (Credentials $credentials) {
            // Make sure that credentials is always an array.
            if (blank($credentials->credentials)) {
                $credentials->credentials = [];
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Indicates if the credentials can be used in the context of the given team.
     */
    public function canBeUsedByTeam(Team $team): bool
    {
        if ($this->provider === Provider::Github) {
            return false;
        }

        if (! $team->hasUser($this->user)) {
            return false;
        }

        return is_null($this->team_id) || $this->team()->is($team);
    }

    public function scopeCanBeUsedByTeam(Builder $query, Team $team)
    {
        $query->where(function (Builder $credentialsQuery) use ($team) {
            $credentialsQuery->whereNull('team_id')->orWhere('team_id', $team->id);
        });

        $query->where(function (Builder $credentialsQuery) use ($team) {
            $credentialsQuery->whereHas('user.ownedTeams', fn (Builder $ownedTeams) => $ownedTeams->where($ownedTeams->qualifyColumn('id'), $team->id));
            $credentialsQuery->orWhereHas('user.teams', fn (Builder $teams) => $teams->where($teams->qualifyColumn('id'), $team->id));
        });

    }

    /**
     * Returns the name of the provider.
     */
    public function getProviderNameAttribute(): string
    {
        return $this->provider->name;
    }

    /**
     * Return the name of this model with additionally the provider name.
     */
    public function getNameWithProviderAttribute(): string
    {
        $name = $this->name;
        $provider = $this->provider_name;

        if (Str::contains($name, $provider)) {
            return $name;
        }

        return "{$this->name} ({$provider})";
    }

    public function scopeProvider(Builder $query, $provider)
    {
        $providers = Enum::values($provider);

        return $query->whereIn('provider', $providers);
    }
}
