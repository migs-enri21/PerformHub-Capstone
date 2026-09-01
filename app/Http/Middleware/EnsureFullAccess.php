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
                    => 'Finish sign-up before creating an event.',
                default
                    => 'Complete sign-up to unlock this feature.',
            };

            return redirect()
                ->route($user->isPerformer() ? 'performer.dashboard' : 'organizer.dashboard')
                ->with('warning', $message);
        }

        if ($user && $user->isAwaitingVerification()) {
            return redirect()
                ->route('performer.dashboard')
                ->with('warning', 'Your account is under review. You can edit your portfolio while you wait for admin verification.');
        }

        if ($user && $user->isOrganizer() && ! $user->is_verified) {
            $message = $request->routeIs('organizer.events.create', 'organizer.events.store')
                ? 'You cannot create an event because your account is not verified yet.'
                : 'Your account is not verified yet. Wait for admin approval to unlock this feature.';

            return redirect()
                ->route('organizer.dashboard')
                ->with('warning', $message);
        }

        return $next($request);
    }
}
