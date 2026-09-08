<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users, roles, and security statuses.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($roleId = $request->input('role_id')) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::orderBy('name')->get();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'admins' => User::where('role', 'Super Admin')->orWhere('role', 'HR Administrator')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Store a newly created system user with assigned roles.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:users,employee_code'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $firstRoleId = !empty($validated['role_ids']) ? $validated['role_ids'][0] : null;
        $primaryRole = $firstRoleId ? Role::find($firstRoleId)?->display_name : 'Employee';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_code' => $validated['employee_code'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'role' => $primaryRole ?? 'Employee',
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 'active',
            'email_verified_at' => now(),
        ]);

        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        AuditLog::record(
            'USER_CREATED',
            'security',
            "Created user account '{$user->name}' ({$user->email}) with assigned roles",
            [
                'name' => $user->name,
                'email' => $user->email,
                'employee_code' => $user->employee_code,
                'status' => $user->status,
                'roles' => $validated['role_ids'] ?? [],
            ]
        );

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} created successfully.");
    }

    /**
     * Update user details, role assignments, or account status.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->employee_code = $validated['employee_code'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->department = $validated['department'] ?? $user->department;
        $user->job_title = $validated['job_title'] ?? $user->job_title;
        $user->status = $validated['status'];

        if (!empty($validated['role_ids'])) {
            $firstRoleId = $validated['role_ids'][0];
            $user->role = Role::find($firstRoleId)?->display_name ?? $user->role;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        AuditLog::record(
            'USER_UPDATED',
            'security',
            "Updated user profile & permissions for '{$user->name}'",
            [
                'name' => $user->name,
                'email' => $user->email,
                'employee_code' => $user->employee_code,
                'status' => $user->status,
                'roles' => $validated['role_ids'] ?? [],
            ]
        );

        return redirect()->route('users.index')
            ->with('success', "User account {$user->name} updated successfully.");
    }

    /**
     * Delete user account with safety checks.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own logged-in account.');
        }

        $oldName = $user->name;
        $user->delete();

        AuditLog::record(
            'USER_DELETED',
            'security',
            "Deleted user account '{$oldName}'",
            ['name' => $oldName, 'email' => $user->email]
        );

        return redirect()->route('users.index')
            ->with('success', "User {$oldName} deleted successfully.");
    }

    /**
     * Direct password reset for user account by Administrator.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLog::record(
            'USER_PASSWORD_RESET',
            'security',
            "Reset password for user account '{$user->name}'",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        return redirect()->route('users.index')
            ->with('success', "Password for user {$user->name} has been successfully reset.");
    }

    /**
     * Block, suspend, or activate user account.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot block your own logged-in administrator account.');
        }

        $oldStatus = $user->status;
        $newStatus = $oldStatus === 'active' ? 'suspended' : 'active';

        $user->status = $newStatus;
        $user->save();

        $actionText = $newStatus === 'suspended' ? 'suspended' : 'activated';

        AuditLog::record(
            $newStatus === 'suspended' ? 'USER_SUSPENDED' : 'USER_ACTIVATED',
            'security',
            "Account status {$actionText} for '{$user->name}'",
            ['status' => $newStatus]
        );

        return redirect()->route('users.index')
            ->with('success', "User account {$user->name} has been {$actionText}.");
    }
}
