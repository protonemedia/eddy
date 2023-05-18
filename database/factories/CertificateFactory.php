<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $csr = Dummies::csr();

        return [
            'site_id' => SiteFactory::new(),
            'csr' => $csr->csr,
            'private_key' => $csr->keyPair->privateKey,
            'certificate' => Dummies::certificate(),
        ];
    }

    public function forSite(Site $site): self
    {
        return $this->state([
            'site_id' => $site->id,
        ]);
    }

    public function uploaded(): self
    {
        return $this->state([
            'uploaded_at' => now(),
        ]);
    }

    public function notActive(): self
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
