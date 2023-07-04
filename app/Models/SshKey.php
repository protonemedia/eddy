<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 */
class SshKey extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'public_key' => 'encrypted',
    ];

    protected $fillable = [
        'name', 'public_key',
    ];

    protected static function booted()
    {
        static::creating(function (SshKey $sshKey) {
            $sshKey->fingerprint = static::generateFingerprint($sshKey->public_key);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFingerprintAttribute(): string
    {
        return static::generateFingerprint($this->public_key) ?: '';
    }

    /**
     * https://github.com/violuke/rsa-ssh-key-fingerprint
     */
    public static function generateFingerprint(string $sshPublicKey, FingerprintAlgorithm $hashAlgorithm = FingerprintAlgorithm::Md5): ?string
    {
        if (substr($sshPublicKey, 0, 4) != 'ssh-') {
            return null;
        }

        $content = explode(' ', $sshPublicKey, 3);

        return match ($hashAlgorithm) {
            FingerprintAlgorithm::Md5 => implode(':', str_split(md5(base64_decode($content[1])), 2)),
            FingerprintAlgorithm::Sha256 => base64_encode(hash('sha256', base64_decode($content[1]), true))
        };
    }
}
