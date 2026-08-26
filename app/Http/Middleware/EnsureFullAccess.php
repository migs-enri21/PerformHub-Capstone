<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFullAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasLimitedAccess()) {
            $message = match (true) {
                $request->routeIs('organizer.events.create', 'organizer.events.store')
                    => 'You cannot create an event because your account is not verified yet.',
                $request->routeIs('performer.portfolio.*')
                    => 'You cannot create or manage a portfolio because your account is not verified yet.',
                default
                    => 'Your account is not verified yet. Complete sign-up and wait for admin approval to unlock this feature.',
            };

            return redirect()
                ->route($user->isPerformer() ? 'performer.dashboard' : 'organizer.dashboard')
                ->with('warning', $message);
        }

        return $next($request);
    }
}
