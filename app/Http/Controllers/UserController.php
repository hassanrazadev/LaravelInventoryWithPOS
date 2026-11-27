<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read user', only: ['index', 'show']),
            new Middleware('permission:create user', only: ['create', 'store']),
            new Middleware('permission:update user', only: ['edit', 'update']),
            new Middleware('permission:delete user', only: ['destroy', 'deleteOrRestore']),
        ];
    }

    public function index(): View
    {
        $users = User::withTrashed()
            ->with('roles')
            ->latest()
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(UserFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $isVerified = $request->has('email_verified')
            ? $request->boolean('email_verified')
            : true;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => $isVerified ? now() : null,
            'created_by' => auth()->id(),
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('users.index')
            ->with('status', true)
            ->with('message', 'New user was added.');
    }

    public function show(int $id): View
    {
        $user = User::withTrashed()
            ->with(['roles', 'permissions', 'createdBy', 'updatedBy'])
            ->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit(int $id): View
    {
        $user = User::withTrashed()->with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UserFormRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $user = User::withTrashed()->findOrFail($id);

        $removingAdmin = $user->hasRole('admin') && ! in_array('admin', $validated['roles'], true);
        if ($removingAdmin) {
            $otherAdmins = User::role('admin')->where('id', '!=', $user->id)->count();
            if ($user->id === auth()->id() || $otherAdmins === 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['roles' => 'Cannot remove the admin role from yourself or the last remaining admin.']);
            }
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'updated_by' => auth()->id(),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        if ($request->has('email_verified')) {
            $payload['email_verified_at'] = $request->boolean('email_verified')
                ? ($user->email_verified_at ?? now())
                : null;
        }

        $user->update($payload);
        $user->syncRoles($validated['roles']);

        return redirect()->route('users.index')
            ->with('status', true)
            ->with('message', 'User was updated.');
    }

    public function deleteOrRestore(int $id): JsonResponse
    {
        if ($id === (int) auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot deactivate your own account.',
            ]);
        }

        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore();
        } else {
            $user->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'User status was updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($id === (int) auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot delete your own account.',
            ]);
        }

        $user = User::withTrashed()->findOrFail($id);

        if ($user->hasRole('admin') && User::role('admin')->where('id', '!=', $user->id)->count() === 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete the last remaining admin user.',
            ]);
        }

        $user->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'User was deleted.',
        ]);
    }
}
