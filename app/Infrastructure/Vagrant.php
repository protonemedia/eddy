<?php

namespace App\Infrastructure;

use App\Infrastructure\Entities\Distribution;
use App\Infrastructure\Entities\Image as EntitiesImage;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\Region as EntitiesRegion;
use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\ServerType;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;

class Vagrant implements ServerProvider
{
    private array $sshKeys = [];

    private string $storagePath;

    private Filesystem $filesystem;

    const IP_RANGE = '192.168.60';

    public function __construct(private ProcessRunner $processRunner, string $storagePath)
    {
        $this->storagePath = rtrim($storagePath, '/');

        $this->filesystem = new Filesystem;
        $this->filesystem->ensureDirectoryExists($this->storagePath);
    }

    /**
     * Check if the Vagrant CLI is available.
     */
    public function canConnect(): bool
    {
        return $this->run('vagrant --version')->isSuccessful();
    }

    /**
     * Run a command using the ProcessRunner.
     */
    private function run(string $command, string $cwd = null, int $timeout = 30): ProcessOutput
    {
        if ($cwd) {
            $this->filesystem->ensureDirectoryExists($cwd);
        }

        return $this->processRunner->run(
            Process::command($command)->path($cwd)->timeout($timeout)
        );
    }

    /**
     * Get all available server regions.
     */
    public function findAvailableServerRegions(): Collection
    {
        return Collection::make([
            new EntitiesRegion('localhost', 'localhost'),
        ])->keyBy->id;
    }

    /**
     * Get all available server types by region.
     */
    public function findAvailableServerTypesByRegion(string $regionId): Collection
    {
        return Collection::make([
            new ServerType('ubuntu-2204-1', 1, 1024, 40),
            new ServerType('ubuntu-2204-2', 2, 2048, 40),
            new ServerType('ubuntu-2204-4', 4, 4096, 40),
        ])->keyBy->id;
    }

    /**
     * Get all available server images by region.
     */
    public function findAvailableServerImagesByRegion(string $regionId): Collection
    {
        return Collection::make([
            new EntitiesImage('ubuntu-2204', Distribution::Ubuntu, OperatingSystem::Ubuntu2204),
        ]);
    }

    /**
     * Find an SSH key by its public key.
     *
     * @return \App\Infrastructure\Entities\SshKey|null
     */
    public function findSshKeyByPublicKey(string $publicKey): ?Entities\SshKey
    {
        return Arr::first($this->sshKeys, function (Entities\SshKey $sshKey) use ($publicKey) {
            return $sshKey->publicKey === $publicKey;
        });
    }

    /**
     * Create a new SSH key.
     *
     * @return \App\Infrastructure\Entities\SshKey
     */
    public function createSshKey(string $publicKey): Entities\SshKey
    {
        $id = count($this->sshKeys) + 1;

        return $this->sshKeys[$id] = new Entities\SshKey((string) $id, $publicKey);
    }

    /**
     * Find a free ID for a new server.
     */
    private function generateServerId(): int
    {
        $directories = array_map(function ($directory) {
            return intval(basename($directory));
        }, $this->filesystem->directories($this->storagePath));

        foreach (range(1, 255) as $id) {
            if (! in_array($id, $directories)) {
                return $id;
            }
        }

        throw new Exception('No free ID found.');
    }

    /**
     * Create a new server.
     *
     * @param  array  $sshKeyIds
     */
    public function createServer(string $name, string $regionId, string $typeId, string $imageId, array|string|Collection $sshKeyIds): string
    {
        $sshKeyPublicKeys = Collection::wrap($sshKeyIds)->map(function ($sshKeyId) {
            return $this->sshKeys[$sshKeyId]->publicKey;
        })->filter()->implode(PHP_EOL);

        $serverTypes = $this->findAvailableServerTypesByRegion($regionId);

        /** @var ServerType */
        $type = $serverTypes->firstWhere('id', $typeId) ?: $serverTypes->first();

        $id = (string) $this->generateServerId();
        $ipAddress = $this->getPublicIpv4OfServer($id);

        $vagrantFile = "
Vagrant.configure(\"2\") do |config|
  config.vm.box = \"ubuntu/jammy64\"

  config.vm.provider \"virtualbox\" do |vb|
    vb.name = '{$name}-{$id}'
    vb.memory = {$type->memoryInMb}
    vb.cpus = {$type->cpuCores}
    vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
    vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    vb.customize ['modifyvm', :id, '--ostype', 'Ubuntu_64']
  end

  config.vm.network :private_network, ip: '{$ipAddress}'
  config.vm.network \"forwarded_port\", guest: 22, host: 2222, auto_correct: true, id: \"ssh\"
  config.vm.network \"forwarded_port\", guest: 80, host: 8000, auto_correct: true, id: \"http\"
  config.vm.network \"forwarded_port\", guest: 443, host: 4433, auto_correct: true, id: \"https\"
  config.ssh.forward_agent = true
  config.vm.provision \"shell\", inline: <<-SHELL
cat <<EOF-KEY >> /root/.ssh/authorized_keys
{$sshKeyPublicKeys}
EOF-KEY
  SHELL
end
";

        $this->filesystem->ensureDirectoryExists($path = "{$this->storagePath}/{$id}");
        $this->filesystem->put("{$this->storagePath}/{$id}/Vagrantfile", trim($vagrantFile));

        $processOutput = $this->run('vagrant up', $path, 300);

        if (! $processOutput->isSuccessful()) {
            throw new Exception("Failed to create server: {$processOutput->getBuffer()}");
        }

        return $id;
    }

    /**
     * Create a new server from a provisioned box.
     */
    public function createServerFromBox(string $id, string $name, int $memoryInMb, int $cpuCores, string $box): string
    {
        $ipAddress = $this->getPublicIpv4OfServer($id);

        $sshKeyPublicKeys = '';

        $user = config('eddy.server_defaults.username');

        $vagrantFile = "
Vagrant.configure(\"2\") do |config|
  config.vm.box = \"{$box}\"

  config.vm.provider \"virtualbox\" do |vb|
    vb.name = '{$name}-{$id}'
    vb.memory = {$memoryInMb}
    vb.cpus = {$cpuCores}
    vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
    vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    vb.customize ['modifyvm', :id, '--ostype', 'Ubuntu_64']
  end

  config.vm.network :private_network, ip: '{$ipAddress}'
  config.vm.network \"forwarded_port\", guest: 22, host: 2222, auto_correct: true, id: \"ssh\"
  config.vm.network \"forwarded_port\", guest: 80, host: 8000, auto_correct: true, id: \"http\"
  config.vm.network \"forwarded_port\", guest: 443, host: 4433, auto_correct: true, id: \"https\"
  config.ssh.forward_agent = true
  config.ssh.username = \"{$user}\"
  config.vm.provision \"shell\", inline: <<-SHELL
cat <<EOF-KEY >> /root/.ssh/authorized_keys
{$sshKeyPublicKeys}
EOF-KEY
  SHELL
end
";

        $this->filesystem->ensureDirectoryExists($path = "{$this->storagePath}/{$id}");
        $this->filesystem->put("{$this->storagePath}/{$id}/Vagrantfile", trim($vagrantFile));

        $processOutput = $this->run('vagrant up', $path, 300);

        if (! $processOutput->isSuccessful()) {
            throw new Exception("Failed to create server: {$processOutput->getBuffer()}");
        }

        return $id;
    }

    /**
     * Get a server by its ID.
     *
     * @return \App\Infrastructure\Entities\Server
     */
    public function getServer(string $id): Entities\Server
    {
        $portsOutput = $this->run('vagrant port --machine-readable', "{$this->storagePath}/{$id}");
        $statusOutput = $this->run('vagrant status --machine-readable', "{$this->storagePath}/{$id}");

        $ports = Collection::make(explode(PHP_EOL, $portsOutput->getBuffer()))
            ->filter(fn ($line) => Str::contains($line, ',forwarded_port,'))
            ->mapWithKeys(function ($line) {
                $parts = explode(',', $line);

                $host = array_pop($parts);
                $guest = array_pop($parts);

                return [$guest => $host];
            });

        $vagrantStatusLine = Collection::make(explode(PHP_EOL, $statusOutput->getBuffer()))
            ->firstWhere(fn ($line) => Str::contains($line, ',state,'));

        $status = match (Str::afterLast($vagrantStatusLine, ',')) {
            'running' => ServerStatus::Running,
            'saved' => ServerStatus::Paused,
            'poweroff' => ServerStatus::Stopped,
            default => ServerStatus::Unknown,
        };

        return new Entities\Server(
            id: (string) $id,
            region: $region = $this->findAvailableServerRegions()->first(),
            type: $this->findAvailableServerTypesByRegion($region->id)->first(),
            image: $this->findAvailableServerImagesByRegion($region->id)->first(),
            status: $status,
            metadata: [
                'ports' => $ports->all(),
            ]
        );
    }

    /**
     * Delete a server by its ID.
     */
    public function deleteServer(string $id): void
    {
        $processOutput = $this->run('vagrant destroy --force', $path = "{$this->storagePath}/{$id}");

        if (! $processOutput->isSuccessful()) {
            throw new Exception("Failed to destroy server: {$processOutput->getBuffer()}");
        }

        $this->filesystem->cleanDirectory($path);
        $this->filesystem->deleteDirectory($path);
    }

    /**
     * Get the public IPv4 address of a server.
     *
     * @return string
     */
    public function getPublicIpv4OfServer(string $id): ?string
    {
        $last = intval($id) + 60;

        return static::IP_RANGE.'.'.(string) $last;
    }
}
