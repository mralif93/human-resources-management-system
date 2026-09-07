<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EnsureCentraFlowSessionValid
{
    /**
     * Handle an incoming request.
     *
     * Periodically re-verifies active CentraFlow tokens against CentraFlow /api/v1/me.
     * If the session/token has been revoked or expired on CentraFlow, terminates local session.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only verify authenticated sessions that originated from CentraFlow SSO
        if (Auth::check() && session()->has('centraflow_token')) {
            $token = session('centraflow_token');

            // Periodic verification: Cache verified state for 10 minutes
            $cacheKey = 'cf_token_valid_' . md5($token);

            if (!cache()->has($cacheKey)) {
                $centraflowHost = rtrim(config('services.centraflow.url', config('services.centraflow.host', env('CENTRAFLOW_HOST', 'http://localhost:8004'))), '/');

                try {
                    $response = Http::withToken($token)
                        ->acceptJson()
                        ->timeout(2)
                        ->get($centraflowHost . '/api/v1/me');

                    if ($response->failed() || $response->status() === 401) {
                        // CentraFlow token was revoked or user was suspended!
                        Auth::logout();
                        session()->flush();
                        return redirect()->route('login')->withErrors([
                            'oauth' => 'Your enterprise session has expired or was revoked by administrator.'
                        ]);
                    }

                    // Cache active token confirmation for 10 minutes
                    cache()->put($cacheKey, true, now()->addMinutes(10));
                } catch (\Exception $e) {
                    // Graceful degradation on temporary network blips: do not prematurely lock user out
                }
            }
        }

        return $next($request);
    }
}
