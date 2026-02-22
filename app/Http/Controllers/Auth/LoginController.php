<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\Logging\ActivityLogger;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * User Authentication — handles login with role-based redirection. After successful login,
 * admins see the main dashboard, while project managers are redirected to their assigned project.
 * Supports standard web login with session management.
 *
 * Data Flow:
 *   Login form → Validate credentials → Create session → Role check → Redirect to
 *   dashboard or project page
 *
 * @depends-on User, AuthenticatesUsers
 * @business-domain Authentication
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
        $this->middleware('guest')->except('logout');
    }

    protected $redirectTo = '/dashboard';

    /**
     * Handle user login.
     */
    protected function authenticated(Request $request, $user)
    {
        $this->activityLogger->log('auth', 'login', $user, [
            'description' => 'User logged in via web.',
        ]);

        if ($user->role === UserRole::VENDOR->value) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'error' => 'Vendor login is not allowed in this portal.',
            ]);
        }

        Session::forget('project_id');

        if ($user->project_id) {
            session(['project_id' => $user->project_id]);
            Session::save();
        }

        if (
            in_array($user->role, [
                UserRole::SITE_ENGINEER->value,
                UserRole::STORE_INCHARGE->value,
                UserRole::REVIEW_MEETING_ONLY->value
            ])
        ) {
            return redirect()->route('meets.index');
        }

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Logout information.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        $this->activityLogger->log('auth', 'logout', $user, [
            'description' => 'User logged out via web.',
        ]);

        // Replicate the default AuthenticatesUsers::logout behaviour
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
