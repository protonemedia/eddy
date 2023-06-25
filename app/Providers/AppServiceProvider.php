<?php

namespace App\Providers;

use App\Http\Resources\CredentialsResource;
use App\Http\Resources\CronResource;
use App\Http\Resources\DaemonResource;
use App\Http\Resources\DatabaseResource;
use App\Http\Resources\DatabaseUserResource;
use App\Http\Resources\FirewallRuleResource;
use App\Http\Resources\ServerResource;
use App\Http\Resources\SiteResource;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\Credentials;
use App\Models\Cron;
use App\Models\Daemon;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\Deployment;
use App\Models\FirewallRule;
use App\Models\Server;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
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

        Str::macro('generateWordpressKey', function ($length = 64) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
            $max = strlen($chars) - 1;

            $key = '';

            for ($i = 0; $i < $length; $i++) {
                $key .= substr($chars, random_int(0, $max), 1);
            }

            return $key;
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
            'cron' => Cron::class,
            'daemon' => Daemon::class,
            'database_user' => DatabaseUser::class,
            'database' => Database::class,
            'deployment' => Deployment::class,
            'firewall_rule' => FirewallRule::class,
            'server' => Server::class,
            'site' => Site::class,
            'team' => Team::class,
            'user' => User::class,
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
            Credentials::class => CredentialsResource::class,
            Cron::class => CronResource::class,
            Daemon::class => DaemonResource::class,
            Database::class => DatabaseResource::class,
            DatabaseUser::class => DatabaseUserResource::class,
            FirewallRule::class => FirewallRuleResource::class,
            Server::class => ServerResource::class,
            Site::class => SiteResource::class,
            Team::class => TeamResource::class,
            User::class => UserResource::class,
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
