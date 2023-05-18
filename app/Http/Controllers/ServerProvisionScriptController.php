<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Provider;
use App\Tasks\AuthorizePublicKey;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerProvisionScriptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Server $server)
    {
        $teamSubscriptionOptions = $server->team->subscriptionOptions();

        if ($teamSubscriptionOptions->mustVerifySubscription() && ! $teamSubscriptionOptions->onTrialOrIsSubscribed()) {
            abort(402, 'Your team must have an active subscription to perform this action.');
        }

        if ($server->provider !== Provider::CustomServer) {
            return '';
        }

        $task = new AuthorizePublicKey($server, $server->public_key, true);

        return $task->getScript();
    }
}
