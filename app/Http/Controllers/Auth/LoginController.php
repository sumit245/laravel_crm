<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    use AuthenticatesUsers;
    protected $redirectTo = '/dashboard';
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        // $this->middleware('auth')->only('logout');
    }
    /**
     * Handle user login with project_id validation.
     */
    protected function authenticated(Request $request, $user)
    {
        // If user is NOT an admin (role 0) and NOT a project manager (role 2), check project_id
        if (!in_array($user->role, [0, 2]) && is_null($user->project_id)) {
            Auth::logout(); // Log out the user
            return redirect()->route('login')->withErrors([
                'error' => 'Access denied! Your project is not assigned yet.',
            ]);
        }

        // Store project_id in session for filtering project-specific data
        if ($user->project_id) {
            session(['project_id' => $user->project_id]);
        }

        return redirect()->intended($this->redirectTo);
    }
}
