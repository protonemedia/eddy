<?php

namespace App\Models;

use App\Events\SiteUpdated;
use App\Jobs\CreateDeployment;
use App\Jobs\DeploySite;
use App\Jobs\UpdateSiteCaddyfile;
use App\Server\PhpVersion;
use App\Server\SiteFiles;
use App\Tasks\Task;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\PendingTask;

/**
 * @property int $port
 * @property Certificate|null $activeCertificate
 * @property Deployment|null $latestDeployment
 * @property Server $server
 */
class Site extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'deploy_key_private' => 'encrypted',
        'deploy_key_public' => 'encrypted',
        'installed_at' => 'datetime',
        'php_version' => PhpVersion::class,
        'shared_directories' => AsArrayObject::class,
        'shared_files' => AsArrayObject::class,
        'type' => SiteType::class,
        'tls_setting' => TlsSetting::class,
        'pending_tls_update_since' => 'datetime',
        'pending_caddyfile_update_since' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
        'writeable_directories' => AsArrayObject::class,
        'zero_downtime_deployment' => 'boolean',
        'deployment_releases_retention' => 'integer',
    ];

    protected $fillable = [
        'address',
        'php_version',
        'type',
        'web_folder',
        'zero_downtime_deployment',
        'deployment_releases_retention',
        'repository_url',
        'repository_branch',
        'shared_directories',
        'shared_files',
        'writeable_directories',
        'deploy_notification_email',
        'hook_before_updating_repository',
        'hook_after_updating_repository',
        'hook_before_making_current',
        'hook_after_making_current',
    ];

    protected $dispatchesEvents = [
        'updated' => SiteUpdated::class,
    ];

    protected static function booted()
    {
        static::creating(function (Site $site) {
            $site->deploy_token = Str::random(32);
            $site->shared_directories ??= [];
            $site->writeable_directories ??= [];
            $site->shared_files ??= [];
        });
    }

    /**
     * Returns the formatted PHP version
     */
    public function phpVersionFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->php_version->formattedVersion(),
        )->withoutObjectCaching();
    }

    /**
     * Returns the HTTP port for this site.
     */
    public function port(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tls_setting === TlsSetting::Off ? 80 : 443,
        )->withoutObjectCaching();
    }

    /**
     * Returns the URL for this site.
     */
    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->tls_setting === TlsSetting::Off ? 'http://' : 'https://').$this->address,
        )->withoutObjectCaching();
    }

    /**
     * Returns a boolean whether the address of the site starts with "www".
     */
    public function startsWithWww(): bool
    {
        return Str::startsWith($this->address, 'www.');
    }

    /**
     * Returns a boolean indicating if the site is currently being deployed.
     */
    public function isDeploying(): bool
    {
        return $this->latestDeployment?->status === DeploymentStatus::Pending;
    }

    /**
     * Returns the directory where the site's logs are stored.
     */
    public function getLogsDirectory(): string
    {
        return "{$this->path}/logs";
    }

    /**
     * Generates a path to the site's web folder.
     */
    public function generateWebDirectory(string $folder): string
    {
        $folder = trim($folder, '/');

        $path = $this->zero_downtime_deployment
            ? "{$this->path}/current/{$folder}"
            : "{$this->path}/repository/{$folder}";

        return rtrim($path, '/');
    }

    /**
     * Returns the path to the site's web folder.
     */
    public function getWebDirectory(): string
    {
        return $this->generateWebDirectory($this->web_folder);
    }

    /**
     * Returns an instance of the ServerTaskDispatcher for this site's user with the given task.
     */
    public function runTaskAsUser(string|Task|PendingTask $task): ServerTaskDispatcher
    {
        return $this->server->runTask($task)->asUser($this->user);
    }

    /**
     * Uploads the given file.
     */
    public function uploadAsUser(string $path, string $contents, bool $throw = false): bool
    {
        return $this->server->uploadAsUser($path, $contents, $this->user, $throw);
    }

    /**
     * Returns a key-value array with the site's default environment variables.
     */
    public function generateEnvironmentVariables(): array
    {
        $variables = [];

        if ($this->type === SiteType::Laravel) {
            $variables['APP_KEY'] = 'base64:'.base64_encode(Encrypter::generateKey('AES-256-CBC'));
            $variables['APP_URL'] = $this->tls_setting === TlsSetting::Off ? "http://{$this->address}" : "https://{$this->address}";
        }

        if ($this->type === SiteType::Wordpress) {
            $vars = [
                'AUTH_KEY',
                'AUTH_SALT',
                'LOGGED_IN_KEY',
                'LOGGED_IN_SALT',
                'NONCE_KEY',
                'NONCE_SALT',
                'SECURE_AUTH_KEY',
                'SECURE_AUTH_SALT',
            ];

            foreach ($vars as $var) {
                $variables[$var] = str_replace(['&', '!', '$'], ['\&', '\!', '\$'], Str::generateWordpressKey());
            }
        }

        return $variables;
    }

    /**
     * Create a 'Deployment' record in the database and dispatch the 'DeploySite' job.
     */
    public function deploy(array $environmentVariables = [], User $user = null): Deployment
    {
        if ($this->fresh()->latestDeployment?->status === DeploymentStatus::Pending) {
            throw new PendingDeploymentException($this);
        }

        /** @var Deployment */
        $deployment = $this->deployments()->create([
            'status' => DeploymentStatus::Pending,
            'user_id' => $user?->exists ? $user->id : null,
        ]);

        $this->server->team->activityLogs()->create([
            'subject_id' => $this->getKey(),
            'subject_type' => $this->getMorphClass(),
            'description' => __(__("Deployed site ':address' on server ':server'", ['address' => $this->address, 'server' => $this->server->name])),
            'user_id' => $user?->exists ? $user->id : null,
        ]);

        dispatch(new DeploySite($deployment, $environmentVariables));

        return $deployment;
    }

    /**
     * Updates the site's Caddyfile with the given PHP version and web folder.
     */
    public function updateCaddyfile(PhpVersion $phpVersion, string $webFolder, ?User $user = null): void
    {
        $this->pending_caddyfile_update_since = now();
        $this->save();

        $site = $this->fresh();
        $user = $user ? $user->fresh() : null;

        Bus::chain([
            new UpdateSiteCaddyfile(
                site: $site,
                phpVersion: $phpVersion,
                webFolder: $webFolder,
                user: $user
            ),
            new CreateDeployment($site, $user),
        ])->dispatch();
    }

    /**
     * Returns the notifiable that needs to be notified about the site's deployment.
     */
    public function deployNotifiable()
    {
        if (! $this->deploy_notification_email) {
            return;
        }

        return $this->server->team->allUsers()->firstWhere('email', $this->deploy_notification_email)
            ?: Notification::route('mail', $this->deploy_notification_email);
    }

    /**
     * Returns an instance of SiteFiles to manage the site's files.
     */
    public function files(): SiteFiles
    {
        return new SiteFiles($this);
    }

    //

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function activeCertificate()
    {
        return $this->hasOne(Certificate::class)
            ->where((new Certificate)->qualifyColumn('is_active'), true)
            ->ofMany();
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function latestDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany();
    }

    public function latestFinishedDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany()->where(
            (new Deployment)->qualifyColumn('status'), DeploymentStatus::Finished
        );
    }
}
