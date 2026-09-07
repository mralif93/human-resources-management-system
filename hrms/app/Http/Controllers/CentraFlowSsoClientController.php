<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowSsoClientController extends Controller
{
    /**
     * Redirect the user to CentraFlow SSO authorization dialog.
     */
    public function redirect(Request $request)
    {
        // 1. Generate CSRF state token & PKCE
        $state = Str::random(40);
        $codeVerifier = Str::random(128);
        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');

        $request->session()->put('oauth_state', $state);
        $request->session()->put('centraflow_oauth_state', $state);
        $request->session()->put('centraflow_code_verifier', $codeVerifier);

        $host = rtrim(config('services.centraflow.url', config('services.centraflow.host', env('CENTRAFLOW_HOST', 'http://localhost:8004'))), '/');

        // 2. Build OAuth authorization query
        $query = http_build_query([
            'client_id'             => config('services.centraflow.client_id', env('CENTRAFLOW_CLIENT_ID')),
            'redirect_uri'          => config('services.centraflow.redirect_uri', env('CENTRAFLOW_REDIRECT_URI')),
            'response_type'         => 'code',
            'scope'                 => config('services.centraflow.scopes', env('CENTRAFLOW_SCOPES', 'hrms:read hrms:write')),
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect()->away($host . '/oauth/authorize?' . $query);
    }

    /**
     * Handle the OAuth callback from CentraFlow.
     */
    public function callback(Request $request)
    {
        // 1. Verify CSRF state token
        $savedState = $request->session()->pull('centraflow_oauth_state') ?? $request->session()->pull('oauth_state');
        $codeVerifier = $request->session()->pull('centraflow_code_verifier');

        if (empty($savedState) || $savedState !== $request->query('state')) {
            return redirect()->route('login')->withErrors(['oauth' => 'Invalid or expired OAuth state token.']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['oauth' => 'CentraFlow authorization was denied.']);
        }

        $host = rtrim(config('services.centraflow.url', config('services.centraflow.host', env('CENTRAFLOW_HOST', 'http://localhost:8004'))), '/');
        $clientId = config('services.centraflow.client_id', env('CENTRAFLOW_CLIENT_ID'));
        $clientSecret = config('services.centraflow.client_secret', env('CENTRAFLOW_CLIENT_SECRET'));
        $redirectUri = config('services.centraflow.redirect_uri', env('CENTRAFLOW_REDIRECT_URI'));

        // 2. Exchange authorization code for access token
        $tokenParams = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $request->query('code'),
        ];
        if ($codeVerifier) {
            $tokenParams['code_verifier'] = $codeVerifier;
        }

        $tokenResponse = Http::asForm()->post($host . '/oauth/token', $tokenParams);

        if (! $tokenResponse->successful()) {
            return redirect()->route('login')->withErrors(['oauth' => 'Could not exchange code with CentraFlow: ' . $tokenResponse->body()]);
        }

        $tokenPayload = $tokenResponse->json();
        $accessToken = $tokenPayload['access_token'];

        // 3. Retrieve user profile from CentraFlow master directory
        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get($host . '/api/v1/me');

        if (! $userResponse->successful()) {
            return redirect()->route('login')->withErrors(['oauth' => 'Failed retrieving profile from CentraFlow.']);
        }

        $profile = $userResponse->json('data') ?? $userResponse->json();

        // FR-HRMS-01: Access Control Guard
        $systemKey = config('services.centraflow.system_key', 'hrms');
        $clearance = $profile['access_control'][$systemKey] ?? null;
        if (! $clearance || ! ($clearance['allowed'] ?? false)) {
            return redirect()->route('login')->withErrors([
                'oauth' => 'Access Denied: You do not have permission to access the PulseHR portal. Contact your CentraFlow administrator.'
            ]);
        }

        // FR-HRMS-02: Role Resolution (prefer access_control.hrms.role, then subsystem_roles.hrms, then fallback)
        $roleMap = [
            'superadmin'         => 'Super Admin',
            'hr_manager'         => 'HR Administrator',
            'department_manager' => 'Department Manager',
            'payroll_officer'    => 'Employee',
            'finance_officer'    => 'Employee',
            'employee'           => 'Employee',
        ];
        $resolvedRole = $clearance['role'] 
            ?? ($profile['subsystem_roles']['hrms'] 
            ?? ($roleMap[$profile['role'] ?? ''] ?? 'Employee'));

        // FR-HRMS-03: Just-In-Time (JIT) Staff Profile Synchronization (Standardized across all 3 sub-systems)
        $user = User::firstOrNew(['email' => $profile['email']]);
        $user->name = $profile['name'] ?? 'CentraFlow User';
        $user->employee_code = $profile['employee_code'] ?? ($profile['staff_id'] ?? $user->employee_code); // Synced from CentraFlow staff_id / employee_code
        $user->designation = $profile['designation'] ?? ($profile['job_title'] ?? $user->designation); // Synced from CentraFlow job_title / designation
        $user->job_title = $profile['job_title'] ?? ($profile['designation'] ?? $user->job_title);
        $user->department = $profile['department'] ?? $user->department;
        $user->phone = $profile['phone'] ?? $user->phone;
        $user->status = ($profile['status'] ?? 'active') === 'active' ? 'active' : 'inactive';
        $user->role = $clearance['role'] ?? ($resolvedRole ?? 'Employee');
        $user->centraflow_uuid = $profile['uuid'] ?? null;

        if (! $user->exists) {
            $user->password = bcrypt(Str::random(32));
        }

        $user->save();

        // FR-HRMS-04: Session Permissions and Central Token Caching
        $request->session()->put('centraflow_token', $accessToken);
        $request->session()->put('centraflow_token_id', $tokenPayload['token_id'] ?? null);
        $request->session()->put('centraflow_access_token', $accessToken);
        $request->session()->put('centraflow_permissions', $clearance['permissions'] ?? []);

        // 5. Authenticate user into local session (true to remember session)
        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
