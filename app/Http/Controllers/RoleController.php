<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read role', only: ['index']),
            new Middleware('permission:create role', only: ['create', 'store']),
            new Middleware('permission:update role', only: ['edit', 'update']),
            new Middleware('permission:delete role', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissionGroups = $this->groupedPermissions();

        return view('roles.create', compact('permissionGroups'));
    }

    public function store(RoleFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('status', true)
            ->with('message', 'New role was created.');
    }

    public function edit(int $id): View
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissionGroups = $this->groupedPermissions();
        $assignedPermissions = $role->permissions->pluck('name')->all();

        return view('roles.edit', compact('role', 'permissionGroups', 'assignedPermissions'));
    }

    public function update(RoleFormRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $role = Role::findOrFail($id);

        if ($role->name === 'admin' && $validated['name'] !== 'admin') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'The admin role cannot be renamed.']);
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('status', true)
            ->with('message', 'Role was updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->name === 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'The admin role cannot be deleted.',
            ]);
        }

        if ($role->users_count > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete a role currently assigned to users.',
            ]);
        }

        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role was deleted.',
        ]);
    }

    /**
     * Group permissions by module suffix (e.g., "create category" -> "category").
     */
    private function groupedPermissions(): Collection
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission): string {
                $parts = preg_split('/\s+/', trim($permission->name), -1, PREG_SPLIT_NO_EMPTY);

                return count($parts) > 1 ? strtolower(end($parts)) : 'other';
            })
            ->sortKeys();
    }
}
