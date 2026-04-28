<?php

namespace App\Policies;

use App\Models\HealthProcedure;
use App\Models\Animal;
use App\Models\User;

/**
 * Misma lógica que VaccinationPolicy:
 * - Solo VETERINARIAN puede escribir procedimientos
 * - INSPECTOR / COORDINATOR pueden leer
 * - CITIZEN solo ve los de sus animales
 */
class HealthProcedurePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    public function viewAny(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $animal->user_id === $user->id;
    }

    public function view(User $user, HealthProcedure $procedure): bool
    {
        return $user->hasAnyRole(['VETERINARIAN', 'INSPECTOR', 'COORDINATOR'])
            || $procedure->animal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('VETERINARIAN');
    }

    public function update(User $user, HealthProcedure $procedure): bool
    {
        return $user->hasRole('VETERINARIAN');
    }

    public function delete(User $user, HealthProcedure $procedure): bool
    {
        return $user->hasRole('VETERINARIAN');
    }
}