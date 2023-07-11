<?php

use App\Http\Controllers\AddSshKeyToServerController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupJobController;
use App\Http\Controllers\BackupJobOutputController;
use App\Http\Controllers\CredentialsController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DaemonController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DatabaseUserController;
use App\Http\Controllers\DiskController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FirewallRuleController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\RemoveSshKeyFromServerController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerProviderController;
use App\Http\Controllers\ServerProvisionScriptController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteDeploymentController;
use App\Http\Controllers\SiteDeploymentSettingsController;
use App\Http\Controllers\SiteDeployTokenController;
use App\Http\Controllers\SiteFileController;
use App\Http\Controllers\SiteLogController;
use App\Http\Controllers\SiteSslController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\SshKeyController;
use App\Http\Controllers\TaskWebhookController;
use App\Http\Middleware\VerifySubscriptionStatus;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any('/deploy/{site}/{token}', [SiteDeploymentController::class, 'deployWithToken'])->name('site.deploy-with-token');
Route::post('/backup/{backup}/{token}', [BackupJobController::class, 'store'])->name('backup-job.store');

Route::middleware('signed:relative')->group(function () {
    // Backups...
    Route::get('/backup-job/{backup_job}', [BackupJobController::class, 'show'])->name('backup-job.show');
    Route::patch('/backup-job/{backup_job}', [BackupJobController::class, 'update'])->name('backup-job.update');

    // Provision Script for Custom Servers...
    Route::get('/servers/{server}/provision-script', ServerProvisionScriptController::class)->name('servers.provision-script');

    // Tasks...
    Route::post('/webhook/task/{task}/timeout', [TaskWebhookController::class, 'markAsTimedOut'])->name('webhook.task.mark-as-timed-out');
    Route::post('/webhook/task/{task}/failed', [TaskWebhookController::class, 'markAsFailed'])->name('webhook.task.mark-as-failed');
    Route::post('/webhook/task/{task}/finished', [TaskWebhookController::class, 'markAsFinished'])->name('webhook.task.mark-as-finished');
    Route::post('/webhook/task/{task}/callback', [TaskWebhookController::class, 'callback'])->name('webhook.task.callback');
});

$authMiddleware = [
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    VerifySubscriptionStatus::class,
];

Route::middleware($authMiddleware)->group(function () {
    Route::get('github/redirect', [GithubController::class, 'redirect'])->name('github.redirect');
    Route::get('github/callback', [GithubController::class, 'callback'])->name('github.callback');
    Route::get('github/repositories', [GithubController::class, 'repositories'])->name('github.repositories');
});

Route::middleware('splade')->group(function () use ($authMiddleware) {
    // Registers routes to support password confirmation in Form and Link components...
    Route::spladePasswordConfirmation();

    Route::view('/', 'welcome');

    Route::middleware($authMiddleware)->group(function () {
        Route::view('/no-subscription', 'no-subscription')->name('no-subscription')->withoutMiddleware(VerifySubscriptionStatus::class);
        Route::redirect('/dashboard', '/servers')->name('dashboard');

        Route::resource('credentials', CredentialsController::class)->parameters(['credentials' => 'credentials'])->except('show');
        Route::resource('servers', ServerController::class);
        Route::resource('ssh-keys', SshKeyController::class)->only(['index', 'create', 'store', 'destroy']);

        Route::resource('disks', DiskController::class)->except('show');

        Route::middleware('can:manage,ssh_key')->group(function () {
            Route::get('ssh-keys/{ssh_key}/servers/add', [AddSshKeyToServerController::class, 'create'])->name('ssh-keys.servers.add-form');
            Route::post('ssh-keys/{ssh_key}/servers/add', [AddSshKeyToServerController::class, 'store'])->name('ssh-keys.servers.add');
            Route::get('ssh-keys/{ssh_key}/servers/remove', [RemoveSshKeyFromServerController::class, 'edit'])->name('ssh-keys.servers.remove-form');
            Route::post('ssh-keys/{ssh_key}/servers/remove', [RemoveSshKeyFromServerController::class, 'destroy'])->name('ssh-keys.servers.remove');
        });

        Route::middleware('can:view,credentials')->group(function () {
            Route::get('servers/provider/{credentials}/regions', [ServerProviderController::class, 'regions'])->name('servers.provider.regions');
            Route::get('servers/provider/{credentials}/types/{region}', [ServerProviderController::class, 'types'])->name('servers.provider.types');
            Route::get('servers/provider/{credentials}/images/{region}', [ServerProviderController::class, 'images'])->name('servers.provider.images');
        });

        Route::middleware('can:manage,server')->group(function () {
            Route::resource('servers.backups', BackupController::class);
            Route::get('servers/{server}/backup-job/{backup_job}', BackupJobOutputController::class)
                ->name('servers.backup-jobs.show')
                ->middleware('can:view,backup_job');

            Route::resource('servers.crons', CronController::class);
            Route::resource('servers.daemons', DaemonController::class);
            Route::resource('servers.databases', DatabaseController::class)->except(['show', 'update']);
            Route::resource('servers.database-users', DatabaseUserController::class)->except(['index', 'show']);
            Route::resource('servers.files', FileController::class)->only(['index', 'show', 'edit', 'update']);
            Route::resource('servers.firewall-rules', FirewallRuleController::class);
            Route::resource('servers.sites', SiteController::class);
            Route::get('servers/{server}/logs', LogController::class)->name('servers.logs.index');
            Route::get('servers/{server}/software', [SoftwareController::class, 'index'])->name('servers.software.index');
            Route::post('servers/{server}/software/{software}/default', [SoftwareController::class, 'default'])->name('servers.software.default');
            Route::post('servers/{server}/software/{software}/restart', [SoftwareController::class, 'restart'])->name('servers.software.restart');

            Route::middleware('can:manage,site')
                ->name('servers.sites.')
                ->prefix('servers/{server}/sites/{site}')
                ->group(function () {
                    Route::get('deployment-settings', [SiteDeploymentSettingsController::class, 'edit'])->name('deployment-settings.edit');
                    Route::patch('deployment-settings', [SiteDeploymentSettingsController::class, 'update'])->name('deployment-settings.update');
                    Route::post('deploy-token', SiteDeployTokenController::class)->name('refresh-deploy-token');
                    Route::resource('deployments', SiteDeploymentController::class)->only(['index', 'show', 'store']);
                    Route::get('files', [SiteFileController::class, 'index'])->name('files.index');
                    Route::get('ssl', [SiteSslController::class, 'edit'])->name('ssl.edit');
                    Route::patch('ssl', [SiteSslController::class, 'update'])->name('ssl.update');
                    Route::get('logs', [SiteLogController::class, 'index'])->name('logs.index');
                });
        });
    });
});
