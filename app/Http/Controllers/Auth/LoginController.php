<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }


    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Login Input
        |--------------------------------------------------------------------------
        */

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Allow Username OR Email
        |--------------------------------------------------------------------------
        */

        $field = filter_var(
            $credentials['username'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'username';


        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            $field,
            $credentials['username']
        )->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Username / Email
        |--------------------------------------------------------------------------
        |
        | Do not reveal whether the username/email exists.
        |
        */

        if (! $user) {
            return back()
                ->withErrors([
                    'username' =>
                        'The provided credentials do not match our records.',
                ])
                ->onlyInput('username');
        }


        /*
        |--------------------------------------------------------------------------
        | Check Whether Account Is Active
        |--------------------------------------------------------------------------
        */

        if (! $user->is_active) {
            return back()
                ->withErrors([
                    'username' =>
                        'Your account has been deactivated. Contact the System Administrator.',
                ])
                ->onlyInput('username');
        }


        /*
        |--------------------------------------------------------------------------
        | Check Password
        |--------------------------------------------------------------------------
        */

        if (! Hash::check(
            $credentials['password'],
            $user->password
        )) {

            $failedAttempts =
                (int) $user->failed_login_attempts + 1;


            /*
            |--------------------------------------------------------------------------
            | Third Invalid Login Attempt
            |--------------------------------------------------------------------------
            */

            if ($failedAttempts >= 3) {

                $user->update([
                    'failed_login_attempts' => 3,
                    'is_active' => false,
                ]);

                return back()
                    ->withErrors([
                        'username' =>
                            'Your account has been locked due to multiple unsuccessful login attempts. Please contact the System Administrator.',
                    ])
                    ->onlyInput('username');
            }


            /*
            |--------------------------------------------------------------------------
            | First / Second Invalid Attempt
            |--------------------------------------------------------------------------
            */

            $user->update([
                'failed_login_attempts' =>
                    $failedAttempts,
            ]);

            return back()
                ->withErrors([
                    'username' =>
                        'The provided credentials do not match our records.',
                ])
                ->onlyInput('username');
        }


        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        |
        | Reset failed login attempts.
        |
        */

        $user->update([
            'failed_login_attempts' => 0,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Authenticate User
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $request->boolean('remember')
        );

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLog::log(
            null,
            $user->id,
            'Logged in'
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()->intended(
            route('dashboard')
        );
    }


    public function logout(Request $request)
    {
        ActivityLog::log(
            null,
            Auth::id(),
            'Logged out'
        );

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}