<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Site $site
 */
class Certificate extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'certificate' => 'encrypted',
        'csr' => 'encrypted',
        'is_active' => 'boolean',
        'public_key' => 'encrypted',
        'private_key' => 'encrypted',
        'uploaded_at' => 'datetime',
    ];

    protected $fillable = [
        'certificate',
        'private_key',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * The directory on the server where the certificate is stored.
     */
    public function siteDirectory(): string
    {
        return "{$this->site->path}/certificates/{$this->id}";
    }

    /**
     * The path to the certificate on the server.
     */
    public function certificatePath(): string
    {
        return "{$this->siteDirectory()}/certificate.cert";
    }

    /**
     * The path to the private key on the server.
     */
    public function privateKeyPath(): string
    {
        return "{$this->siteDirectory()}/private.key";
    }
}
