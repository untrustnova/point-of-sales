<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $users = User::where('store_id', $storeId)
            ->with('roles')
            ->latest()
            ->paginate(15);

        $roles = Role::where('name', '!=', 'Owner')->get();

        return Inertia::render('users/index', [
            'users' => $users,
            'available_roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $newUser = User::create([
            'store_id' => $storeId,
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
        ]);

        $newUser->assignRole($validated['role']);

        return Redirect::back()->with('success', 'Staf baru berhasil ditambahkan.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$validated['role']]);

        return Redirect::back()->with('success', 'Role staf berhasil diperbarui.');
    }
}
