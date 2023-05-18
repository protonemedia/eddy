<?php

namespace App\Models;

use App\Events\FirewallRuleDeleted;
use App\Events\FirewallRuleUpdated;
use App\Server\Firewall\RuleAction;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Server $server
 */
class FirewallRule extends Model
{
    use HasFactory;
    use HasUlids;
    use InstallsAsynchronously;

    protected $fillable = [
        'name',
        'port',
        'action',
        'from_ipv4',
    ];

    protected $casts = [
        'action' => RuleAction::class,
        'installed_at' => 'datetime',
        'installation_failed_at' => 'datetime',
        'uninstallation_requested_at' => 'datetime',
        'uninstallation_failed_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'updated' => FirewallRuleUpdated::class,
    ];

    protected static function booted()
    {
        static::deleted(function ($firewallRule) {
            event(new FirewallRuleDeleted($firewallRule->id, $firewallRule->server->team_id));
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Format the rule so ufw can understand it.
     */
    public function formatAsUfwRule(): string
    {
        $from = $this->from_ipv4 ? "from {$this->from_ipv4} to any port" : null;

        return implode(' ', array_filter([
            $this->action->value,
            $from,
            $this->port,
        ], fn ($value) => ! is_null($value)));
    }
}
