<?php

namespace App\Policies;

use App\Models\Vaccination;
use App\Models\Animal;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / VETERINARIAN → acceso total
 * - INSPECTOR / COORDINATOR → solo lectura
 * - CITIZEN → solo ver las vacunas de sus propios animales
 */
class VaccinationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    /** Ver vacunas de un animal */
    public function viewAny(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    public function view(User $user, Vaccination $vaccination): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $vaccination->animal->user_id === $user->id;
    }

    /** Solo VETERINARIAN puede registrar/editar/eliminar vacunas */
    public function create(User $user): bool
    {
        return $user->hasRole('VETERINARIAN');
    }

    public function update(User $user, Vaccination $vaccination): bool
    {
        return $user->hasRole('VETERINARIAN');
    }

    public function delete(User $user, Vaccination $vaccination): bool
    {
        return $user->hasRole('VETERINARIAN');
    }

    /** Ver listado global de vacunas próximas a vencer */
    public function viewUpcoming(User $user): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'COORDINATOR']);
    }
}