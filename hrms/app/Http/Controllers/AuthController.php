<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Display forgot password view.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle password reset email request.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Feedback simulation
        return back()->with('status', 'A reset link has been dispatched to your corporate email address.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $tokenId = $request->session()->get('centraflow_token_id');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Central Single Sign-Out (SLO): Terminate CentraFlow session and return to HRMS login
        $centraflowHost = rtrim(config('services.centraflow.url', config('services.centraflow.host', env('CENTRAFLOW_HOST', 'http://localhost:8004'))), '/');
        $returnUrl = route('login', ['logged_out' => '1']);

        if (! empty($centraflowHost)) {
            $params = ['redirect_uri' => $returnUrl];
            if ($tokenId) {
                $params['token_id'] = $tokenId;
            }

            return redirect()->away($centraflowHost . '/logout?' . http_build_query($params));
        }

        return redirect()->route('login')->with('status', 'You have been logged out securely.');
    }
}
