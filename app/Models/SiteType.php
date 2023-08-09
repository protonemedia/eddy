<?php

namespace App\Models;

enum SiteType: string
{
    case Generic = 'generic';
    case Laravel = 'laravel';
    case Static = 'static';
    case Wordpress = 'wordpress';

    public function getDisplayName(): string
    {
        return $this->name;
    }

    public function defaultAttributes(bool $zeroDowntimeDeployment = true): array
    {
        return match ($this) {
            self::Laravel => $this->laravelDefaults($zeroDowntimeDeployment),
            self::Static => $this->staticDefaults($zeroDowntimeDeployment),
            self::Wordpress => $this->wordpressDefaults(),
            default => [],
        };
    }

    private function laravelDefaults(bool $zeroDowntimeDeployment = true): array
    {
        $installScript = trim('
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm install --prefer-offline --no-audit
npm run build
$PHP_BINARY artisan storage:link
$PHP_BINARY artisan config:cache
$PHP_BINARY artisan route:cache
$PHP_BINARY artisan view:cache
$PHP_BINARY artisan event:cache
# $PHP_BINARY artisan migrate --force
'.($zeroDowntimeDeployment ? '' : '$PHP_BINARY artisan up').'
');

        return [
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
            'hook_before_updating_repository' => $zeroDowntimeDeployment ? '' : '$PHP_BINARY artisan down',
            'hook_after_updating_repository' => $zeroDowntimeDeployment ? '' : $installScript,

            'hook_before_making_current' => $zeroDowntimeDeployment ? $installScript : '',
            'hook_after_making_current' => '',
        ];
    }

    private function staticDefaults(bool $zeroDowntimeDeployment = true)
    {
        $key = $zeroDowntimeDeployment ? 'hook_before_making_current' : 'hook_after_updating_repository';

        return [
            $key => trim('
# npm install --prefer-offline --no-audit
# npm run build
'),
        ];
    }

    private function wordpressDefaults()
    {
        return [
            'web_folder' => '/',
            'zero_downtime_deployment' => false,
            'repository_url' => null,
            'repository_branch' => null,
            'shared_directories' => [],
            'shared_files' => [],
            'writeable_directories' => [],
            'hook_before_updating_repository' => '',
            'hook_after_updating_repository' => '',
            'hook_before_making_current' => '',
            'hook_after_making_current' => '',
        ];
    }
}
