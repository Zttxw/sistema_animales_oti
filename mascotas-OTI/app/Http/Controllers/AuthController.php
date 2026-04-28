<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'identity_document' => 'required|string|max:20|unique:users',
            'birth_date'        => 'nullable|date|before:today',
            'gender'            => ['nullable', Rule::in(['M', 'F', 'O'])],
            'phone'             => 'nullable|string|max:20',
            'email'             => 'required|email|max:150|unique:users',
            'address'           => 'nullable|string|max:255',
            'sector'            => 'nullable|string|max:100',
            'password'          => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'ACTIVE';

        $user = User::create($data);
        $user->assignRole('CITIZEN');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Cuenta suspendida o inactiva.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'LOGIN',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user->load('roles'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        AuditLog::create([
            'user_id'    => $request->user()->id,
            'action'     => 'LOGOUT',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('roles'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user  = User::where('email', $request->email)->first();
        $token = Str::random(64);

        $user->update([
            'recovery_token'            => Hash::make($token),
            'recovery_token_expires_at' => now()->addHour(),
        ]);

        // TODO: dispatch(new SendPasswordResetMail($user, $token));

        return response()->json(['message' => 'Se envió el enlace de recuperación a tu correo.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
            ->where('recovery_token_expires_at', '>', now())
            ->first();

        if (! $user || ! Hash::check($request->token, $user->recovery_token)) {
            return response()->json(['message' => 'Token inválido o expirado.'], 422);
        }

        $user->update([
            'password'                  => Hash::make($request->password),
            'recovery_token'            => null,
            'recovery_token_expires_at' => null,
        ]);

        return response()->json(['message' => 'Contraseña restablecida correctamente.']);
    }
}