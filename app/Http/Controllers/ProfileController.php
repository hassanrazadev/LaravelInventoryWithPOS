<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('user.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'updated_by' => $user->id,
        ]);

        return redirect()->route('profile.edit')
            ->with('status', true)
            ->with('message', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('profile.edit')
            ->with('status', true)
            ->with('message', 'Password updated successfully.');
    }
}
