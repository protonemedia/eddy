<?php

namespace App\Http\Controllers;

use App\Enum;
use App\Jobs\InstallFirewallRule;
use App\Jobs\UninstallFirewallRule;
use App\Models\FirewallRule;
use App\Models\Server;
use App\Rules\FirewallPort;
use App\Server\Firewall\RuleAction;
use Illuminate\Http\Request;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class FirewallRuleController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(FirewallRule::class, 'firewall_rule');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        return view('firewall-rules.index', [
            'server' => $server,
            'firewallRules' => SpladeTable::for($server->firewallRules())
                ->column('name', __('Name'), sortable: true)
                ->column('port', __('Port'), sortable: true)
                ->column('action', __('Action'), sortable: true, as: fn (RuleAction $action) => $action->name)
                ->column('from_ipv4', __('From IP'), sortable: true, as: fn ($ip) => $ip ?: __('Any'))
                ->column('status', __('Status'), alignment: 'right')
                ->withGlobalSearch(columns: ['name', 'port', 'from_ipv4'])
                ->rowModal(fn (FirewallRule $firewallRule) => route('servers.firewall-rules.edit', [$server, $firewallRule]))
                ->defaultSort('name')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Server $server)
    {
        return view('firewall-rules.create', [
            'server' => $server,
            'actions' => Enum::options(RuleAction::class),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Server $server, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', Enum::rule(RuleAction::class)],
            'port' => ['required',  new FirewallPort],
            'from_ipv4' => ['nullable', 'string', 'ipv4'],
        ]);

        /** @var FirewallRule */
        $firewallRule = $server->firewallRules()->create($data);

        dispatch(new InstallFirewallRule($firewallRule, $this->user()));

        $this->logActivity(__("Created firewall rule ':name' on server ':server'", ['name' => $firewallRule->name, 'server' => $server->name]), $firewallRule);

        Toast::message(__('The Firewall Rule has been created and will be installed on the server.'));

        return to_route('servers.firewall-rules.index', $server);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, FirewallRule $firewallRule)
    {
        return view('firewall-rules.edit', [
            'firewallRule' => $firewallRule,
            'server' => $server,
            'actions' => Enum::options(RuleAction::class),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, FirewallRule $firewallRule)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $firewallRule->update($data);

        $this->logActivity(__("Updated firewall rule ':name' on server ':server'", ['name' => $firewallRule->name, 'server' => $server->name]), $firewallRule);

        Toast::message(__('The Firewall Rule name has been updated.'));

        return to_route('servers.firewall-rules.index', $server);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server, FirewallRule $firewallRule)
    {
        $firewallRule->markUninstallationRequest();

        dispatch(new UninstallFirewallRule($firewallRule, $this->user()));

        $this->logActivity(__("Deleted firewall rule ':name' from server ':server'", ['name' => $firewallRule->name, 'server' => $server->name]), $firewallRule);

        Toast::message(__('The Firewall Rule will be uninstalled from the server.'));

        return to_route('servers.firewall-rules.index', $server);
    }
}
