<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

/**
 * Password Confirmation — handles the password re-confirmation flow required before sensitive
 * operations like account deletion or role changes. Ensures the current session user re-enters
 * their password for security.
 *
 * Data Flow:
 *   Sensitive action triggers password confirm → User enters password → Verify against
 *   stored hash → Allow or deny the action
 *
 * @depends-on User
 * @business-domain Authentication
 * @package App\Http\Controllers\Auth
 */
class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}
