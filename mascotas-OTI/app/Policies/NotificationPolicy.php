<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

/**
 * Un usuario solo puede ver y gestionar sus propias notificaciones.
 * ADMIN puede verlas todas (para soporte).
 */
class NotificationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}