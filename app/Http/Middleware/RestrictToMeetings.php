<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictToMeetings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is Site Engineer (1) or Store Incharge (4) - restrict to meetings only
        if (in_array($user->role, [1, 4])) {
            // Allow access only to review-meeting related routes
            $allowedRoutes = [
                'meets.*',
                'whiteboard.*',
                'discussion-points.*',
                'logout',
                'staff.profile',
                'staff.updateProfilePicture',
                'staff.change-password',
                'staff.update-password',
            ];

            $currentRoute = $request->route()->getName();

            // Check if current route is allowed
            $isAllowed = false;
            foreach ($allowedRoutes as $pattern) {
                if (fnmatch($pattern, $currentRoute)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return redirect()->route('meets.index')->with('error', 'You only have access to review meetings.');
            }
        }

        return $next($request);
    }
}
