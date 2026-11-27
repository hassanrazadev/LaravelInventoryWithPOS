<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read permission', only: ['index']),
            new Middleware('permission:create permission', only: ['create', 'store']),
            new Middleware('permission:update permission', only: ['edit', 'update']),
            new Middleware('permission:delete permission', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get();

        return view('permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('permissions.create');
    }

    public function store(PermissionFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')
            ->with('status', true)
            ->with('message', 'New permission was created.');
    }

    public function edit(int $id): View
    {
        $permission = Permission::findOrFail($id);

        return view('permissions.edit', compact('permission'));
    }

    public function update(PermissionFormRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $permission = Permission::findOrFail($id);

        $permission->update([
            'name' => $validated['name'],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')
            ->with('status', true)
            ->with('message', 'Permission was updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status' => true,
            'message' => 'Permission was deleted.',
        ]);
    }
}
