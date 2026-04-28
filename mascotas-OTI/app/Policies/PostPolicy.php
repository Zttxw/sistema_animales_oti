<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Reglas:
 * - ADMIN / COORDINATOR → acceso total incluido moderar y publicar
 * - VETERINARIAN / INSPECTOR → pueden crear posts informativos; no moderar
 * - CITIZEN → puede crear avisos de pérdida y editar los suyos propios
 */
class PostPolicy
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

    public function view(?User $user, Post $post): bool
    {
        // Solo DRAFT requiere ser autor o staff
        if ($post->status === 'DRAFT') {
            if (!$user) return false;
            return $user->hasAnyRole(['COORDINATOR']) || $post->author_id === $user->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return true; // todos pueden crear (ciudadanos: avisos de pérdida; staff: noticias)
    }

    /** Solo el autor o staff puede editar */
    public function update(User $user, Post $post): bool
    {
        if ($user->hasAnyRole(['COORDINATOR', 'VETERINARIAN', 'INSPECTOR'])) {
            return true;
        }

        return $post->author_id === $user->id;
    }

    /** Publicar / destacar / deshabilitar: solo staff editorial */
    public function updateStatus(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'VETERINARIAN', 'INSPECTOR']);
    }

    /** Resolver aviso de pérdida: el dueño del animal o staff */
    public function resolveLostNotice(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['COORDINATOR', 'INSPECTOR'])
            || $post->author_id === $user->id;
    }

    /** Eliminar post */
    public function delete(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['COORDINATOR'])
            || $post->author_id === $user->id;
    }
}