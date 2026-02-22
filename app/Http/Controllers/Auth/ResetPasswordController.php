<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * Password Reset Execution — handles the actual password reset after the user clicks the link
 * from the forgot-password email. Validates the reset token, accepts a new password, and updates
 * the user record.
 *
 * Data Flow:
 *   User clicks reset link → Validate token → Enter new password → Hash and update User
 *   record → Redirect to login
 *
 * @depends-on User
 * @business-domain Authentication
 * @package App\Http\Controllers\Auth
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';
}
