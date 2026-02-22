<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

/**
 * Password Reset Request — handles the "Forgot Password" flow. Users enter their email to
 * receive a password reset link. Sends a reset token via email using Laravel's built-in password
 * broker.
 *
 * Data Flow:
 *   User clicks "Forgot Password" → Enter email → Validate existence → Send reset email
 *   with token → Token stored in password_resets table
 *
 * @depends-on User
 * @business-domain Authentication
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;
}
