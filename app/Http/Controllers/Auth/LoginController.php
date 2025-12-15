<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle user login.
     */
    protected function authenticated(Request $request, $user)
    {
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
}
