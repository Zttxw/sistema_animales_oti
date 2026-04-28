<?php

namespace App\Policies;

use App\Models\StrayAnimal;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / COORDINATOR → acceso total
 * - VETERINARIAN / INSPECTOR → pueden registrar y actualizar estados
 * - CITIZEN → solo puede reportar (crear) y ver; no editar ni eliminar
 */
class StrayAnimalPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StrayAnimal $strayAnimal): bool
    {
        return true;
    }

    /** Cualquier ciudadano autenticado puede reportar un callejero */
    public function create(User $user): bool
    {
        return true;
    }

    /** Editar datos: solo staff */
    public function update(User $user, StrayAnimal $strayAnimal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR']);
    }

    /** Cambiar estado (RESCUED, IN_TREATMENT, etc.): solo staff */
    public function updateStatus(User $user, StrayAnimal $strayAnimal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR']);
    }

    /** Agregar fotos: quien lo reportó o staff */
    public function addPhotos(User $user, StrayAnimal $strayAnimal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $strayAnimal->reported_by === $user->id;
    }

    /** Eliminar (soft-delete): solo COORDINATOR */
    public function delete(User $user, StrayAnimal $strayAnimal): bool
    {
        return $user->hasRole('COORDINATOR');
    }
}