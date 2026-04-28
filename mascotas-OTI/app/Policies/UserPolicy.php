<?php

namespace App\Policies;

use App\Models\User;

/**
 * Reglas:
 * - ADMIN → acceso total
 * - COORDINATOR → puede ver y suspender usuarios; no cambiar roles
 * - Cualquier usuario → puede ver y editar su propio perfil
 */
class UserPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    /** Listar usuarios: solo staff */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'INSPECTOR', 'VETERINARIAN']);
    }

    /** Ver perfil: propio o staff */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id
            || $user->hasAnyRole(['COORDINATOR', 'INSPECTOR', 'VETERINARIAN']);
    }

    /** Crear usuarios desde el panel: solo ADMIN (registro público usa AuthController) */
    public function create(User $user): bool
    {
        return false; // ADMIN ya pasa por before()
    }

    /** Editar perfil: el propio usuario o COORDINATOR */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id
            || $user->hasRole('COORDINATOR');
    }

    /** Suspender / desactivar cuenta */
    public function updateStatus(User $user, User $model): bool
    {
        // Nadie se suspende a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasRole('COORDINATOR');
    }

    /** Cambiar rol: solo ADMIN (ya cubierto por before()) */
    public function updateRole(User $user, User $model): bool
    {
        return false;
    }

    /** Eliminar: no se elimina, se desactiva → mismo permiso que updateStatus */
    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id
            && $user->hasRole('COORDINATOR');
    }
}