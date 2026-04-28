<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);
        $users = User::with('roles')
            ->when($request->search, fn($q) => $q
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name',          'like', "%{$request->search}%")
                ->orWhere('identity_document',  'like', "%{$request->search}%")
                ->orWhere('email',              'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->role,   fn($q) => $q->role($request->role))
            ->paginate(20);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);
        $data = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'identity_document' => 'required|string|max:20|unique:users',
            'birth_date'        => 'nullable|date',
            'gender'            => ['nullable', Rule::in(['M','F','O'])],
            'phone'             => 'nullable|string|max:20',
            'email'             => 'required|email|unique:users',
            'address'           => 'nullable|string|max:255',
            'sector'            => 'nullable|string|max:100',
            'password'          => 'required|string|min:8',
            'role'              => 'required|string|exists:roles,name',
        ]);

        $role = $data['role'];
        unset($data['role']);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->assignRole($role);

        return response()->json($user->load('roles'), 201);
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);
        return response()->json(
            $user->load(['roles', 'animals.species'])
        );
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);
        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:100',
            'last_name'   => 'sometimes|string|max:100',
            'birth_date'  => 'nullable|date',
            'gender'      => ['nullable', Rule::in(['M','F','O'])],
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'sector'      => 'nullable|string|max:100',
            'email'       => ['sometimes','email', Rule::unique('users')->ignore($user->id)],
            'identity_document' => ['sometimes','string','max:20', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($data);

        return response()->json($user->fresh('roles'));
    }

    public function updateStatus(Request $request, User $user)
    {
        Gate::authorize('updateStatus', $user);
        $request->validate([
            'status' => ['required', Rule::in(['ACTIVE','SUSPENDED','INACTIVE'])],
        ]);

        $user->update(['status' => $request->status]);

        return response()->json($user->fresh());
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('updateRole', $user);
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->syncRoles([$request->role]);

        return response()->json($user->fresh('roles'));
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        // Soft-delete lógico: marcamos como INACTIVE
        $user->update(['status' => 'INACTIVE']);

        return response()->json(['message' => 'Usuario desactivado correctamente.']);
    }
}