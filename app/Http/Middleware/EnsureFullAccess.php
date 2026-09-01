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
            return redirect()
                ->route($user->isPerformer() ? 'performer.dashboard' : 'organizer.dashboard')
                ->with('warning', 'Complete your sign-up to unlock this feature. You can continue anytime from your dashboard.');
        }

        if ($user && $user->isAwaitingVerification()) {
            return redirect()
                ->route('performer.dashboard')
                ->with('warning', 'Your account is under review. You can edit your portfolio while you wait for admin verification.');
        }

        return $next($request);
    }
}
