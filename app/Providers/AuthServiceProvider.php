<?php

namespace App\Providers;

use App\Models;
use App\Policies;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Models\Backup::class => Policies\BackupPolicy::class,
        Models\BackupJob::class => Policies\BackupJobPolicy::class,
        Models\Credentials::class => Policies\CredentialsPolicy::class,
        Models\Cron::class => Policies\CronPolicy::class,
        Models\Daemon::class => Policies\DaemonPolicy::class,
        Models\Database::class => Policies\DatabasePolicy::class,
        Models\DatabaseUser::class => Policies\DatabaseUserPolicy::class,
        Models\Deployment::class => Policies\DeploymentPolicy::class,
        Models\Disk::class => Policies\DiskPolicy::class,
        Models\FirewallRule::class => Policies\FirewallRulePolicy::class,
        Models\Server::class => Policies\ServerPolicy::class,
        Models\Site::class => Policies\SitePolicy::class,
        Models\SshKey::class => Policies\SshKeyPolicy::class,
        Models\Team::class => Policies\TeamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
