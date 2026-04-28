<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / COORDINATOR → pueden moderar y eliminar cualquier comentario
 * - CITIZEN / cualquier rol → puede editar y eliminar solo sus propios comentarios
 */
class CommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('ADMIN') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /** Solo el autor puede editar su comentario */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    /** El autor puede eliminar el suyo; COORDINATOR puede eliminar cualquiera */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id
            || $user->hasRole('COORDINATOR');
    }

    /** Moderar (ocultar/deshabilitar): solo ADMIN / COORDINATOR */
    public function moderate(User $user): bool
    {
        return $user->hasRole('COORDINATOR');
    }
}