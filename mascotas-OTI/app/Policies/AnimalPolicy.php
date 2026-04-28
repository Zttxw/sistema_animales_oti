<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / VETERINARIAN / INSPECTOR / COORDINATOR → acceso total
 * - CITIZEN → solo sus propios animales; no puede eliminar permanentemente
 */
class AnimalPolicy
{
    // Antes de cualquier check: ADMIN pasa siempre
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    /** Ver listado general */
    public function viewAny(User $user): bool
    {
        return true; // todos los autenticados pueden listar
    }

    /** Ver un animal específico */
    public function view(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    /** Registrar un nuevo animal */
    public function create(User $user): bool
    {
        return true; // cualquier usuario autenticado puede registrar
    }

    /** Editar datos del animal */
    public function update(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    /** Cambiar estado (LOST, FOR_ADOPTION, DECEASED, ACTIVE) */
    public function updateStatus(User $user, Animal $animal): bool
    {
        // CITIZEN solo puede reportar su propio animal como perdido
        if ($user->hasRole('CITIZEN')) {
            return $animal->user_id === $user->id;
        }

        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR']);
    }

    /** Eliminar (soft-delete) */
    public function delete(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    /** Ver historial completo */
    public function viewHistory(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    /** Gestionar fotos */
    public function managePhotos(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }
}