<?php

namespace App\Jobs;

use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\ServerProvider;
use App\Models\Server;
use App\Provider;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class CreateServerOnInfrastructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Server $server)
    {
        if ($server->provider === Provider::Vagrant) {
            $this->timeout = 300;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->server->provider === Provider::CustomServer) {
            $this->server->forceFill(['status' => ServerStatus::Starting])->save();

            return;
        }

        if ($this->server->provider_id) {
            // Server already created
            return;
        }

        $client = $this->server->getProvider();

        $sshKeys = $this->sshKeys($client);

        if ($this->server->provider === Provider::HetznerCloud) {
            // Hetzner needs some time to create the SSH keys
            sleep(10);
        }

        $providerId = $client->createServer(
            name: Str::slug($this->server->name),
            regionId: $this->server->region,
            typeId: $this->server->type,
            imageId: $this->server->image,
            sshKeyIds: $sshKeys
        );

        $this->server->forceFill([
            'provider_id' => $providerId,
            'status' => ServerStatus::Starting,
        ])->save();
    }

    /**
     * Gathers the SSH keys to add to the server.
     *
     * @return mixed
     */
    public function sshKeys(ServerProvider $client)
    {
        $keys = [];

        $testPublicKey = config('services.test_public_key');

        if ($testPublicKey) {
            $keys[] = ($client->findSshKeyByPublicKey($testPublicKey) ?: $client->createSshKey($testPublicKey))->id;
        }

        $keys[] = ($client->findSshKeyByPublicKey($this->server->public_key) ?: $client->createSshKey($this->server->public_key))->id;

        return count($keys) > 1 ? $keys : Arr::first($keys);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $errorMessage = null;

        if ($this->server->provider === Provider::HetznerCloud && $exception instanceof ClientException) {
            $httpResponse = new Response($exception->getResponse());

            $errorMessage = $this->mapHetznerErrorCode($httpResponse->json('error.message') ?: '');
        }

        dispatch(new CleanupFailedServerProvisioning($this->server, errorMessage: $errorMessage));
    }

    /**
     * Maps the Hetzner error code to a human readable error message.
     *
     * @see https://docs.hetzner.cloud/#errors
     */
    private function mapHetznerErrorCode(string $code): ?string
    {
        return match ($code) {
            'conflict' => 'The resource has changed during the request, please retry',
            'forbidden' => 'Insufficient permissions for this request',
            'invalid_input' => 'Error while parsing or processing the input',
            'json_error' => 'Invalid JSON input in your request',
            'locked' => 'The item you are trying to access is locked (there is already an Action running)',
            'maintenance' => 'Cannot perform operation due to maintenance',
            'not_found' => 'Entity not found',
            'protected' => 'The Action you are trying to start is protected for this resource',
            'rate_limit_exceeded' => 'Error when sending too many requests',
            'resource_limit_exceeded' => 'Error when exceeding the maximum quantity of a resource for an account',
            'resource_unavailable' => 'The requested resource is currently unavailable',
            'service_error' => 'Error within a service',
            'token_readonly' => 'The token is only allowed to perform GET requests',
            'unavailable' => 'A service or product is currently not available',
            'uniqueness_error' => 'One or more of the objects fields must be unique',
            'unsupported_error' => 'The corresponding resource does not support the Action',
            default => null
        };
    }
}
