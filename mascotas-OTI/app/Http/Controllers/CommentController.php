<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with('user')
            ->visible()
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($comments);
    }

    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        if ($post->status !== 'PUBLISHED' && $post->status !== 'FEATURED') {
            return response()->json(['message' => 'No se puede comentar en este post.'], 422);
        }

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'status' => 'VISIBLE',
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, Post $post, Comment $comment)
    {
        $this->ensureBelongsToPost($comment, $post);
        Gate::authorize('update', $comment); // Solo el autor puede editar

        $request->validate(['content' => 'required|string|max:2000']);
        $comment->update(['content' => $request->content]);

        return response()->json($comment->fresh('user'));
    }

    /**
     * Moderar comentario (ADMIN/COORDINATOR).
     */
    public function moderate(Request $request, Post $post, Comment $comment)
    {
        $this->ensureBelongsToPost($comment, $post);
        Gate::authorize('moderate', Comment::class);

        $request->validate([
            'status' => ['required', Rule::in(['VISIBLE', 'HIDDEN', 'DELETED'])],
            'moderation_reason' => 'nullable|string|max:255',
        ]);

        $comment->update([
            'status' => $request->status,
            'moderated_by' => Auth::id(),
            'moderation_reason' => $request->moderation_reason,
        ]);

        return response()->json($comment->fresh());
    }

    public function destroy(Post $post, Comment $comment)
    {
        $this->ensureBelongsToPost($comment, $post);
        Gate::authorize('delete', $comment);
        $comment->update(['status' => 'DELETED']);
        return response()->json(['message' => 'Comentario eliminado.']);
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function ensureBelongsToPost(Comment $comment, Post $post): void
    {
        if ($comment->post_id !== $post->id) {
            abort(404, 'Comentario no pertenece a este post.');
        }
    }
}