<?php

namespace App\Models;

use App\Provider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;

/**
 * @property Collection<int, Credentials> $credentials
 * @property Collection<int, SshKey> $sshKeys
 * @property Team $currentTeam
 * @property Credentials|null $githubCredentials
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasTeams;
    use HasUlids;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasProfilePhoto;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    public function getInitialsAttribute()
    {
        $name = $this->name;
        $initials = '';

        $parts = array_filter(explode(' ', $name));

        foreach ($parts as $word) {
            $initials .= $word[0];
        }

        return strtoupper($initials);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return  array<string, string>|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        return [$this->email => $this->name];
    }

    public function sshKeys()
    {
        return $this->hasMany(SshKey::class)->orderBy(
            (new SshKey)->qualifyColumn('name')
        );
    }

    public function credentials()
    {
        return $this->hasMany(Credentials::class)->orderBy(
            (new Credentials)->qualifyColumn('name')
        );
    }

    public function githubCredentials()
    {
        return $this->credentials()->one()->where('provider', Provider::Github);
    }

    /**
     * Returns a boolean whether this user has a Github credentials.
     */
    public function hasGithubCredentials(): bool
    {
        return $this->githubCredentials()->exists();
    }
}
