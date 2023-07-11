<?php

namespace App\Providers;

use App\Http\Resources;
use App\Models;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use ProtoneMedia\LaravelXssProtection\Middleware\XssCleanInput;
use ProtoneMedia\Splade\Components\Form\Select;
use ProtoneMedia\Splade\Facades\Splade;
use ProtoneMedia\Splade\SpladeTable;
use ProtoneMedia\Splade\SpladeToast;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->enableSafetyMechanisms();
        $this->setSpladeDefaults();

        $this->app->bind(GithubProvider::class, fn () => Socialite::driver('github'));

        $this->app->bind(IntlMoneyFormatter::class, function () {
            return new IntlMoneyFormatter(
                formatter: new \NumberFormatter('en_US', \NumberFormatter::CURRENCY),
                currencies: new ISOCurrencies
            );
        });

        /** @var Factory */
        $validatorFactory = app(Factory::class);

        // Set value names whenever a new Validator instance is created.
        $validatorFactory->resolver(function (Translator $translator, array $data, array $rules, array $messages, array $attributes) {
            return tap(new Validator($translator, $data, $rules, $messages, $attributes), function (Validator $validator) {
                $validator->setValueNames(trans('validation.value_names', []));
            });
        });

        XssCleanInput::skipWhen(function (Request $request) {
            return $request->routeIs('servers.files.update');
        });

        Arr::macro('explodePaths', function ($value = null) {
            return Collection::make(explode(PHP_EOL, $value ?? ''))
                ->map(fn ($item) => rtrim(trim($item), '/'))
                ->filter(fn ($item) => $item !== '')
                ->unique()
                ->values()
                ->all();
        });

        Str::macro('generateWordpressKey', function ($length = 64) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
            $max = strlen($chars) - 1;

            $key = '';

            for ($i = 0; $i < $length; $i++) {
                $key .= substr($chars, random_int(0, $max), 1);
            }

            return $key;
        });

        URL::macro('relativeSignedRoute', function (string $name, mixed $parameters = []): string {
            $host = rtrim(config('eddy.webhook_url') ?: config('app.url'), '/');

            return $host.URL::signedRoute($name, $parameters, absolute: false);
        });
    }

    private function enableSafetyMechanisms()
    {
        if ($this->app->runningInConsole()) {
            // Log slow commands.
            $this->app[ConsoleKernel::class]->whenCommandLifecycleIsLongerThan(
                5000,
                function ($startedAt, $input, $status) {
                    // TODO: Add info about the command
                    Log::warning('A command took longer than 5 seconds.');
                }
            );
        } else {
            // Log slow requests.
            $this->app[HttpKernel::class]->whenRequestLifecycleIsLongerThan(
                5000,
                function ($startedAt, $request, $response) {
                    // TODO: Add info about the request
                    Log::warning('A request took longer than 5 seconds.');
                }
            );
        }

        // Everything strict, all the time.
        Model::shouldBeStrict();

        // But in production, log the violation instead of throwing an exception.
        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);

                Log::info("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }

        // Enforce a morph map instead of making it optional.
        Relation::enforceMorphMap([
            'backup_job' => Models\BackupJob::class,
            'backup' => Models\Backup::class,
            'cron' => Models\Cron::class,
            'daemon' => Models\Daemon::class,
            'database_user' => Models\DatabaseUser::class,
            'database' => Models\Database::class,
            'deployment' => Models\Deployment::class,
            'disk' => Models\Disk::class,
            'firewall_rule' => Models\FirewallRule::class,
            'server' => Models\Server::class,
            'site' => Models\Site::class,
            'team' => Models\Team::class,
            'user' => Models\User::class,
        ]);

        DB::listen(function ($query) {
            if ($query->time > 1000) {
                Log::warning('An individual database query exceeded 1 second.', [
                    'sql' => $query->sql,
                ]);
            }
        });
    }

    private function setSpladeDefaults()
    {
        Select::defaultChoices();
        Select::defaultSelectFirstRemoteOption();

        Splade::requireTransformer()->transformUsing([
            Models\Backup::class => Resources\BackupResource::class,
            Models\Credentials::class => Resources\CredentialsResource::class,
            Models\Cron::class => Resources\CronResource::class,
            Models\Daemon::class => Resources\DaemonResource::class,
            Models\Database::class => Resources\DatabaseResource::class,
            Models\DatabaseUser::class => Resources\DatabaseUserResource::class,
            Models\Disk::class => Resources\DiskResource::class,
            Models\FirewallRule::class => Resources\FirewallRuleResource::class,
            Models\Server::class => Resources\ServerResource::class,
            Models\Site::class => Resources\SiteResource::class,
            Models\Team::class => Resources\TeamResource::class,
            Models\User::class => Resources\UserResource::class,
        ]);

        Splade::defaultToast(function (SpladeToast $toast) {
            return $toast->autoDismiss(10);
        });

        Splade::defaultModalCloseExplicitly();

        SpladeTable::defaultColumnCanBeHidden(false);
        SpladeTable::hidePaginationWhenResourceContainsOnePage();
        SpladeTable::defaultHighlightFirstColumn();
    }
}
