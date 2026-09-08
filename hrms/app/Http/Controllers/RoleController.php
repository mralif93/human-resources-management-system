<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of system roles and assigned permissions.
     */
    public function index(Request $request): View
    {
        $query = Role::with(['permissions', 'users']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('name')->paginate(10)->withQueryString();
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', trim($validated['name']))),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        AuditLog::record(
            'ROLE_CREATED',
            'security',
            "Created security role '{$role->display_name}' ({$role->name})",
            [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'permissions' => $validated['permission_ids'] ?? [],
            ]
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->display_name}' created successfully.");
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->display_name = $validated['display_name'];
        $role->description = $validated['description'] ?? null;
        $role->save();

        if (isset($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        AuditLog::record(
            'ROLE_UPDATED',
            'security',
            "Updated security role '{$role->display_name}'",
            [
                'name' => $role->name,
                'permissions' => $validated['permission_ids'] ?? [],
            ]
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->display_name}' updated successfully.");
    }

    /**
     * Remove a role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', "System role '{$role->display_name}' is protected and cannot be deleted.");
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', "Cannot delete role '{$role->display_name}' because it has active assigned users.");
        }

        $oldName = $role->display_name;
        $role->delete();

        AuditLog::record(
            'ROLE_DELETED',
            'security',
            "Deleted custom role '{$oldName}'",
            ['name' => $role->name]
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$oldName}' has been removed.");
    }
}
