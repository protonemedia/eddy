<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\AddSshKeyToServerController;
use App\Http\Controllers\CredentialsController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DaemonController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DatabaseUserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FirewallRuleController;
use App\Http\Controllers\RemoveSshKeyFromServerController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerProviderController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteDeploymentController;
use App\Http\Controllers\SiteDeploymentSettingsController;
use App\Http\Controllers\SiteSslController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\SshKeyController;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use AdditionalAssertions;

    /** @test */
    public function it_uses_the_correct_policies()
    {
        // Credentials
        $this->assertActionUsesMiddleware(CredentialsController::class, 'create', ['can:create,App\Models\Credentials']);
        $this->assertActionUsesMiddleware(CredentialsController::class, 'destroy', ['can:delete,credentials']);
        $this->assertActionUsesMiddleware(CredentialsController::class, 'edit', ['can:update,credentials']);
        $this->assertActionUsesMiddleware(CredentialsController::class, 'index', ['can:viewAny,App\Models\Credentials']);
        $this->assertActionUsesMiddleware(CredentialsController::class, 'store', ['can:create,App\Models\Credentials']);
        $this->assertActionUsesMiddleware(CredentialsController::class, 'update', ['can:update,credentials']);

        // Cron
        $this->assertActionUsesMiddleware(CronController::class, 'create', ['can:manage,server', 'can:create,App\Models\Cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'destroy', ['can:manage,server', 'can:delete,cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'edit', ['can:manage,server', 'can:update,cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'index', ['can:manage,server', 'can:viewAny,App\Models\Cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'show', ['can:manage,server', 'can:view,cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'store', ['can:manage,server', 'can:create,App\Models\Cron']);
        $this->assertActionUsesMiddleware(CronController::class, 'update', ['can:manage,server', 'can:update,cron']);

        // Daemon
        $this->assertActionUsesMiddleware(DaemonController::class, 'create', ['can:manage,server', 'can:create,App\Models\Daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'destroy', ['can:manage,server', 'can:delete,daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'edit', ['can:manage,server', 'can:update,daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'index', ['can:manage,server', 'can:viewAny,App\Models\Daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'show', ['can:manage,server', 'can:view,daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'store', ['can:manage,server', 'can:create,App\Models\Daemon']);
        $this->assertActionUsesMiddleware(DaemonController::class, 'update', ['can:manage,server', 'can:update,daemon']);

        // Database
        $this->assertActionUsesMiddleware(DatabaseController::class, 'create', ['can:manage,server', 'can:create,App\Models\Database']);
        $this->assertActionUsesMiddleware(DatabaseController::class, 'destroy', ['can:manage,server', 'can:delete,database']);
        $this->assertActionUsesMiddleware(DatabaseController::class, 'edit', ['can:manage,server', 'can:update,database']);
        $this->assertActionUsesMiddleware(DatabaseController::class, 'index', ['can:manage,server', 'can:viewAny,App\Models\Database']);
        $this->assertActionUsesMiddleware(DatabaseController::class, 'store', ['can:manage,server', 'can:create,App\Models\Database']);

        // Database User
        $this->assertActionUsesMiddleware(DatabaseUserController::class, 'create', ['can:manage,server', 'can:create,App\Models\DatabaseUser']);
        $this->assertActionUsesMiddleware(DatabaseUserController::class, 'destroy', ['can:manage,server', 'can:delete,database_user']);
        $this->assertActionUsesMiddleware(DatabaseUserController::class, 'edit', ['can:manage,server', 'can:update,database_user']);
        $this->assertActionUsesMiddleware(DatabaseUserController::class, 'store', ['can:manage,server', 'can:create,App\Models\DatabaseUser']);
        $this->assertActionUsesMiddleware(DatabaseUserController::class, 'update', ['can:manage,server', 'can:update,database_user']);

        // File
        $this->assertActionUsesMiddleware(FileController::class, 'edit', ['can:manage,server']);
        $this->assertActionUsesMiddleware(FileController::class, 'index', ['can:manage,server']);
        $this->assertActionUsesMiddleware(FileController::class, 'show', ['can:manage,server']);
        $this->assertActionUsesMiddleware(FileController::class, 'update', ['can:manage,server']);

        // Firewall Rule
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'create', ['can:manage,server', 'can:create,App\Models\FirewallRule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'destroy', ['can:manage,server', 'can:delete,firewall_rule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'edit', ['can:manage,server', 'can:update,firewall_rule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'index', ['can:manage,server', 'can:viewAny,App\Models\FirewallRule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'show', ['can:manage,server', 'can:view,firewall_rule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'store', ['can:manage,server', 'can:create,App\Models\FirewallRule']);
        $this->assertActionUsesMiddleware(FirewallRuleController::class, 'update', ['can:manage,server', 'can:update,firewall_rule']);

        // Server
        $this->assertActionUsesMiddleware(ServerController::class, 'create', ['can:create,App\Models\Server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'destroy', ['can:delete,server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'edit', ['can:update,server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'index', ['can:viewAny,App\Models\Server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'show', ['can:view,server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'store', ['can:create,App\Models\Server']);
        $this->assertActionUsesMiddleware(ServerController::class, 'update', ['can:update,server']);

        // Server
        $this->assertActionUsesMiddleware(ServerProviderController::class, 'images', ['can:view,credentials']);
        $this->assertActionUsesMiddleware(ServerProviderController::class, 'regions', ['can:view,credentials']);
        $this->assertActionUsesMiddleware(ServerProviderController::class, 'types', ['can:view,credentials']);

        // Site
        $this->assertActionUsesMiddleware(SiteController::class, 'create', ['can:manage,server', 'can:create,App\Models\Site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'destroy', ['can:manage,server', 'can:delete,site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'edit', ['can:manage,server', 'can:update,site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'index', ['can:manage,server', 'can:viewAny,App\Models\Site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'show', ['can:manage,server', 'can:view,site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'store', ['can:manage,server', 'can:create,App\Models\Site']);
        $this->assertActionUsesMiddleware(SiteController::class, 'update', ['can:manage,server', 'can:update,site']);

        // Site Deployment
        $this->assertActionUsesMiddleware(SiteDeploymentController::class, 'index', ['can:manage,server', 'can:manage,site', 'can:viewAny,App\Models\Deployment']);
        $this->assertActionUsesMiddleware(SiteDeploymentController::class, 'show', ['can:manage,server', 'can:manage,site', 'can:view,deployment']);
        $this->assertActionUsesMiddleware(SiteDeploymentController::class, 'store', ['can:manage,server', 'can:manage,site', 'can:create,App\Models\Deployment']);

        // Site Deployment Settings
        $this->assertActionUsesMiddleware(SiteDeploymentSettingsController::class, 'edit', ['can:manage,server', 'can:manage,site']);
        $this->assertActionUsesMiddleware(SiteDeploymentSettingsController::class, 'update', ['can:manage,server', 'can:manage,site']);

        // Site Ssl
        $this->assertActionUsesMiddleware(SiteSslController::class, 'edit', ['can:manage,server', 'can:manage,site']);
        $this->assertActionUsesMiddleware(SiteSslController::class, 'update', ['can:manage,server', 'can:manage,site']);

        // Software
        $this->assertActionUsesMiddleware(SoftwareController::class, 'index', ['can:manage,server']);
        $this->assertActionUsesMiddleware(SoftwareController::class, 'restart', ['can:manage,server']);

        // Ssh Key
        $this->assertActionUsesMiddleware(SshKeyController::class, 'create', ['can:create,App\Models\SshKey']);
        $this->assertActionUsesMiddleware(SshKeyController::class, 'destroy', ['can:delete,ssh_key']);
        $this->assertActionUsesMiddleware(SshKeyController::class, 'index', ['can:viewAny,App\Models\SshKey']);
        $this->assertActionUsesMiddleware(SshKeyController::class, 'store', ['can:create,App\Models\SshKey']);

        // Ssh Key Server
        $this->assertActionUsesMiddleware(AddSshKeyToServerController::class, 'create', ['can:manage,ssh_key']);
        $this->assertActionUsesMiddleware(AddSshKeyToServerController::class, 'store', ['can:manage,ssh_key']);
        $this->assertActionUsesMiddleware(RemoveSshKeyFromServerController::class, 'edit', ['can:manage,ssh_key']);
        $this->assertActionUsesMiddleware(RemoveSshKeyFromServerController::class, 'destroy', ['can:manage,ssh_key']);
    }
}
