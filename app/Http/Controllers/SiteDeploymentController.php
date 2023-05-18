<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Server;
use App\Models\Site;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteDeploymentController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Deployment::class, 'deployment');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server, Site $site)
    {
        $query = $site->deployments();

        $maxDeployments = $server->team->subscriptionOptions()->maxDeploymentsPerSite();

        if ($maxDeployments !== false) {
            $lastVisibleDeployment = $site->deployments()->latest()->skip($maxDeployments - 1)->value('created_at');

            $lastVisibleDeployment ? $query->where('created_at', '>=', $lastVisibleDeployment) : null;
        }

        return view('sites.deployments.index', [
            'server' => $server,
            'site' => $site,
            'deployments' => SpladeTable::for($query)
                ->column('updated_at', __('Deployed at'), sortable: true)
                ->column('user.name', __('User'), as: fn ($name) => $name ?: __('Via Deploy URL'))
                ->column('short_git_hash', __('Git Hash'))
                ->column('status', __('Status'), alignment: 'right')
                ->withGlobalSearch(__('Search Git Hash...'), ['git_hash'])
                ->rowLink(fn (Deployment $deployment) => route('servers.sites.deployments.show', [$server, $site, $deployment]))
                ->defaultSort('-updated_at')
                ->paginate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Server $server, Site $site)
    {
        $deployment = $site->deploy(user: $this->user());

        Toast::success(__('Deployment queued.'));

        return to_route('servers.sites.deployments.show', [$server, $site, $deployment]);
    }

    /**
     * Deploy the site with the given token.
     */
    public function deployWithToken(Site $site, string $token)
    {
        if ($token !== $site->deploy_token) {
            abort(403);
        }

        $teamSubscriptionOptions = $site->server->team->subscriptionOptions();

        if ($teamSubscriptionOptions->mustVerifySubscription() && ! $teamSubscriptionOptions->onTrialOrIsSubscribed()) {
            abort(402, 'Your team must have an active subscription to perform this action.');
        }

        $site->deploy();

        return response()->noContent(200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server, Site $site, Deployment $deployment)
    {
        return view('sites.deployments.show', [
            'server' => $server,
            'site' => $site,
            'deployment' => $deployment,
        ]);
    }
}
