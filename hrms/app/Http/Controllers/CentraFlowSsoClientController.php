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
        // 1. Generate CSRF state token
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        // 2. Build OAuth authorization query
        $query = http_build_query([
            'client_id'     => config('services.centraflow.client_id', env('CENTRAFLOW_CLIENT_ID')),
            'redirect_uri'  => config('services.centraflow.redirect_uri', env('CENTRAFLOW_REDIRECT_URI')),
            'response_type' => 'code',
            'scope'         => config('services.centraflow.scopes', env('CENTRAFLOW_SCOPES', '')),
            'state'         => $state,
        ]);

        $authUrl = rtrim(config('services.centraflow.host', env('CENTRAFLOW_HOST')), '/') . '/oauth/authorize?' . $query;

        return redirect()->away($authUrl);
    }

    /**
     * Handle the OAuth callback from CentraFlow.
     */
    public function callback(Request $request)
    {
        // 1. Verify CSRF state token
        $savedState = $request->session()->pull('oauth_state');
        if (empty($savedState) || $savedState !== $request->query('state')) {
            abort(403, 'Invalid or expired OAuth state token.');
        }

        if ($request->has('error')) {
            return redirect('/login')->withErrors(['oauth' => 'CentraFlow authorization was denied.']);
        }

        // 2. Exchange authorization code for access token
        $tokenResponse = Http::asForm()->post(rtrim(config('services.centraflow.host', env('CENTRAFLOW_HOST')), '/') . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.centraflow.client_id', env('CENTRAFLOW_CLIENT_ID')),
            'client_secret' => config('services.centraflow.client_secret', env('CENTRAFLOW_CLIENT_SECRET')),
            'redirect_uri'  => config('services.centraflow.redirect_uri', env('CENTRAFLOW_REDIRECT_URI')),
            'code'          => $request->query('code'),
        ]);

        if (! $tokenResponse->successful()) {
            return redirect('/login')->withErrors(['oauth' => 'Could not exchange code with CentraFlow: ' . $tokenResponse->body()]);
        }

        $tokenPayload = $tokenResponse->json();
        $accessToken = $tokenPayload['access_token'];

        // 3. Retrieve user profile from CentraFlow master directory
        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get(rtrim(config('services.centraflow.host', env('CENTRAFLOW_HOST')), '/') . '/api/v1/me');

        if (! $userResponse->successful()) {
            return redirect('/login')->withErrors(['oauth' => 'Failed retrieving profile from CentraFlow.']);
        }

        $profile = $userResponse->json('data');

        // Role mapping from CentraFlow to HRMS
        $roleMap = [
            'superadmin'         => 'Super Admin',
            'hr_manager'         => 'HR Administrator',
            'department_manager' => 'Department Manager',
            'payroll_officer'    => 'Employee',
            'finance_officer'    => 'Employee',
            'employee'           => 'Employee',
        ];
        $mappedRole = $profile['hrms_role'] ?? ($roleMap[$profile['role'] ?? ''] ?? ($profile['role'] ?? 'Employee'));

        // 4. Find or provision user in local sub-system database
        $user = User::firstOrNew(['email' => $profile['email']]);
        if (! $user->exists) {
            $user->name = $profile['name'] ?? 'CentraFlow User';
            $user->password = bcrypt(Str::random(32));
            $user->role = $mappedRole;
            $user->department = $profile['department'] ?? null;
            $user->job_title = $profile['job_title'] ?? null;
            $user->employee_code = $profile['employee_code'] ?? null;
            $user->save();
        } else {
            $user->name = $profile['name'] ?? $user->name;
            if (!empty($mappedRole) && $user->role === 'Employee') {
                $user->role = $mappedRole;
            }
            if (!empty($profile['department'])) {
                $user->department = $profile['department'];
            }
            if (!empty($profile['job_title'])) {
                $user->job_title = $profile['job_title'];
            }
            if (!empty($profile['employee_code'])) {
                $user->employee_code = $profile['employee_code'];
            }
            $user->save();
        }

        // Optional: Save CentraFlow UUID or role if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'centraflow_uuid')) {
            $user->centraflow_uuid = $profile['uuid'];
            $user->save();
        }

        // 5. Authenticate user into local session (no permanent remember-me cookie)
        Auth::login($user, false);

        // Store token in session if sub-system needs to call CentraFlow APIs
        $request->session()->put('centraflow_access_token', $accessToken);

        return redirect()->intended('/dashboard');
    }
}
