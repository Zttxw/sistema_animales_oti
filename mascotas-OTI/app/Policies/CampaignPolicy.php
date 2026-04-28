<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / COORDINATOR → acceso total (crear, editar, cancelar, gestionar asistencia)
 * - VETERINARIAN / INSPECTOR → pueden ver y registrar acciones; no crear/cancelar
 * - CITIZEN → solo puede inscribirse y ver campañas publicadas
 */
class CampaignPolicy
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

    public function view(?User $user, Campaign $campaign): bool
    {
        // CITIZEN o unauthenticated solo ve campañas publicadas o en progreso
        if (!$user || $user->hasRole('CITIZEN')) {
            return in_array($campaign->status, ['PUBLISHED', 'IN_PROGRESS', 'FINISHED']);
        }

        return true;
    }

    /** Crear campañas: COORDINATOR o superior */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN']);
    }

    /** Editar campaña: quien la creó o COORDINATOR */
    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasRole('COORDINATOR')
            || $campaign->created_by === $user->id;
    }

    /** Cambiar estado (publicar, iniciar, finalizar, cancelar) */
    public function updateStatus(User $user, Campaign $campaign): bool
    {
        return $user->hasRole('COORDINATOR')
            || $campaign->created_by === $user->id;
    }

    /** Inscribir participantes */
    public function registerParticipant(User $user, Campaign $campaign): bool
    {
        // CITIZEN solo puede inscribirse si la campaña está publicada
        if ($user->hasRole('CITIZEN')) {
            return $campaign->status === 'PUBLISHED';
        }

        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN', 'INSPECTOR']);
    }

    /** Marcar asistencia: solo staff */
    public function markAttendance(User $user): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN', 'INSPECTOR']);
    }
}