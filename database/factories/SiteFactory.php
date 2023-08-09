<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\Site;
use App\Models\SiteType;
use App\Models\TlsSetting;
use App\Server\PhpVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'type' => SiteType::Generic,
            'server_id' => ServerFactory::new(),
            'address' => $domain = $this->faker->domainName,
            'zero_downtime_deployment' => true,
            'repository_url' => null,
            'repository_branch' => 'main',
            'user' => config('eddy.server_defaults.username'),
            'web_folder' => '/public',
            'php_version' => PhpVersion::Php81,
            'tls_setting' => TlsSetting::Auto,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Site $site) {
            if (! $site->getAttributeValue('path')) {
                $site->path = "/home/{$site->user}/{$site->address}";
            }
        });
    }

    public function installed(): self
    {
        return $this->state([
            'installed_at' => now(),
        ]);
    }

    public function notInstalled(): self
    {
        return $this->state([
            'installed_at' => null,
        ]);
    }

    public function laravelApp(): self
    {
        return $this->state([
            'type' => SiteType::Laravel,
            'php_version' => PhpVersion::Php81,
            'web_folder' => '/public',
            'shared_directories' => ['storage'],
            'shared_files' => ['.env'],
            'writeable_directories' => [
                'bootstrap/cache',
                'storage',
                'storage/app',
                'storage/app/public',
                'storage/framework',
                'storage/framework/cache',
                'storage/framework/sessions',
                'storage/framework/views',
                'storage/logs',
            ],
            'hook_before_updating_repository' => '',
            'hook_after_updating_repository' => '',
            'hook_before_making_current' => '
composer install
npm install --prefer-offline --no-audit
npm run build
$PHP_BINARY artisan storage:link
$PHP_BINARY artisan config:cache
$PHP_BINARY artisan route:cache
$PHP_BINARY artisan view:cache
$PHP_BINARY artisan event:cache
# $PHP_BINARY artisan migrate --force
',
            'hook_after_making_current' => '',
        ]);
    }

    public function wordpressApp(): self
    {
        return $this->state([
            'type' => SiteType::Wordpress,
            'php_version' => PhpVersion::Php81,
            'web_folder' => '/',
            'shared_directories' => [],
            'shared_files' => ['wp-config.php'],
            'writeable_directories' => [],
            'hook_before_updating_repository' => '',
            'hook_after_updating_repository' => '',
            'hook_before_making_current' => '',
            'hook_after_making_current' => '',
        ]);
    }

    public function forRepository(string $url = 'git@github.com:protonemedia/eddy.git', string $branch = 'main'): self
    {
        return $this->state([
            'repository_url' => $url,
            'repository_branch' => $branch,
        ]);
    }

    public function forServer(Server $server): self
    {
        return $this->state([
            'server_id' => $server,
        ]);
    }

    public function withDeployKey(): self
    {
        $keyPair = Dummies::ed25519KeyPair();

        return $this->state([
            'deploy_key_public' => $keyPair->publicKey,
            'deploy_key_private' => $keyPair->privateKey,
        ]);
    }
}
