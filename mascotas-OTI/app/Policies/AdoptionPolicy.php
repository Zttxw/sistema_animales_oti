<?php

namespace App\Policies;

use App\Models\Adoption;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / COORDINATOR → acceso total
 * - VETERINARIAN → puede crear y editar, no cambiar estado final
 * - INSPECTOR → solo lectura
 * - CITIZEN → solo puede ver adopciones disponibles (público)
 */
class AdoptionPolicy
{
    public function before(?User $user): ?bool
    {
        if ($user && $user->hasRole('ADMIN')) return true;
        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Adoption $adoption): bool
    {
        return true; // adopciones son públicas
    }

    /** Publicar un animal en adopción */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN']);
    }

    /** Editar datos de la ficha de adopción */
    public function update(User $user, Adoption $adoption): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN']);
    }

    /**
     * Aprobar / concretar / retirar adopción.
     * Es un cambio de estado con impacto legal → solo COORDINATOR.
     */
    public function updateStatus(User $user, Adoption $adoption): bool
    {
        return $user->hasRole('COORDINATOR');
    }
}