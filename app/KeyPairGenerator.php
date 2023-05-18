<?php

namespace App;

use App\Tasks\GenerateEd25519KeyPair;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class KeyPairGenerator
{
    public function ed25519(): KeyPair
    {
        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists(storage_path('app/keygen'));

        $file = Str::random();

        $privatePath = storage_path("app/keygen/{$file}");
        $publicPath = storage_path("app/keygen/{$file}.pub");

        $task = new GenerateEd25519KeyPair($privatePath);

        throw_unless(
            $task->pending()->dispatch()->isSuccessful(),
            new Exception('Failed to generate ed25519 key pair.')
        );

        $privateKey = $filesystem->get($privatePath);
        $publicKey = $filesystem->get($publicPath);

        $filesystem->delete([$privatePath, $publicPath]);

        return new KeyPair($privateKey, $publicKey, KeyPairType::Ed25519);
    }
}
