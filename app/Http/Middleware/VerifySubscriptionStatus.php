<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Team $team */
        $team = $request->user()->currentTeam;

        $subscriptionOptions = $team->subscriptionOptions();

        if ($subscriptionOptions->mustVerifySubscription() && ! $subscriptionOptions->onTrialOrIsSubscribed()) {
            return redirect()->route('no-subscription');
        }

        return $next($request);
    }
}
