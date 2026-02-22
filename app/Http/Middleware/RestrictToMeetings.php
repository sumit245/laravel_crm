<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Meeting access control middleware. Restricts access to meeting-related routes to only users who
 * are authorized to view or manage meetings (typically admins and project managers). Custom
 * business middleware.
 *
 * Data Flow:
 *   HTTP Request to meeting route → Check user role/permissions → Authorized: proceed →
 *   Unauthorized: abort 403
 *
 * @depends-on User
 * @business-domain Security
 * @package App\Http\Middleware
 */
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

        // If user is Site Engineer (1), Store Incharge (4), or Review Meeting Only (11) - restrict to meetings only
        if (in_array($user->role, [1, 4, 11])) {
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
