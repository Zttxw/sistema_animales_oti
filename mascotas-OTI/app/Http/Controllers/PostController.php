<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\LostNotice;
use App\Models\PostPhoto;
use App\Models\Animal;
use App\Models\AnimalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['postType', 'author', 'photos'])
            ->when($request->post_type_id, fn($q) => $q->where('post_type_id', $request->post_type_id))
            ->when($request->status,       fn($q) => $q->where('status',       $request->status))
            ->when($request->author_id,    fn($q) => $q->where('author_id',    $request->author_id))
            ->when($request->search,       fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Post::class);
        $data = $request->validate([
            'post_type_id' => 'required|exists:post_types,id',
            'title'        => 'required|string|max:200',
            'content'      => 'nullable|string',
            'photos'       => 'nullable|array',
            'photos.*'     => 'url|max:500',
            // Campos adicionales si es aviso de pérdida
            'animal_id'    => 'required_if:is_lost_notice,true|nullable|exists:animals,id',
            'lost_at'      => 'required_if:is_lost_notice,true|nullable|date',
            'lost_location'=> 'required_if:is_lost_notice,true|nullable|string',
            'contact'      => 'nullable|string|max:150',
            'is_lost_notice' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($data, $request) {
            $post = Post::create([
                'post_type_id' => $data['post_type_id'],
                'title'        => $data['title'],
                'content'      => $data['content'] ?? null,
                'author_id'    => Auth::id(),
                'status'       => 'DRAFT',
            ]);

            // Fotos del post
            if (! empty($data['photos'])) {
                PostPhoto::insert(array_map(fn($url) => [
                    'post_id'    => $post->id,
                    'url'        => $url,
                    'created_at' => now(),
                ], $data['photos']));
            }

            // Aviso de pérdida
            if ($request->boolean('is_lost_notice')) {
                LostNotice::create([
                    'post_id'       => $post->id,
                    'animal_id'     => $data['animal_id'],
                    'lost_at'       => $data['lost_at'],
                    'lost_location' => $data['lost_location'],
                    'contact'       => $data['contact'] ?? null,
                    'status'        => 'ACTIVE',
                ]);

                Animal::find($data['animal_id'])->update(['status' => 'LOST']);

                AnimalHistory::create([
                    'animal_id'   => $data['animal_id'],
                    'user_id'     => Auth::id(),
                    'change_type' => 'STATUS',
                    'description' => 'Animal reportado como perdido.',
                ]);
            }

            return $post;
        });

        $post = Post::with(['postType', 'photos', 'lostNotice'])->latest()->first();
        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        return response()->json(
            $post->load(['postType', 'author', 'photos', 'lostNotice.animal', 'comments.user'])
        );
    }

    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);
        $data = $request->validate([
            'title'   => 'sometimes|string|max:200',
            'content' => 'nullable|string',
        ]);

        $post->update($data);
        return response()->json($post->fresh(['postType', 'photos']));
    }

    public function updateStatus(Request $request, Post $post)
    {
        Gate::authorize('updateStatus', $post);
        $request->validate([
            'status' => ['required', Rule::in(['DRAFT','PUBLISHED','DISABLED','FEATURED'])],
        ]);

        $post->update(['status' => $request->status]);
        return response()->json($post->fresh());
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);
        $post->delete();
        return response()->json(['message' => 'Publicación eliminada correctamente.']);
    }

    /**
     * Marcar aviso de perdida como resuelto (animal encontrado).
     */
    public function resolveLostNotice(Request $request, Post $post)
    {
        Gate::authorize('resolveLostNotice', $post);
        $notice = $post->lostNotice;

        if (! $notice) {
            return response()->json(['message' => 'Este post no tiene aviso de pérdida.'], 404);
        }

        $request->validate([
            'status' => ['required', Rule::in(['FOUND','CLOSED'])],
        ]);

        DB::transaction(function () use ($request, $notice) {
            $notice->update(['status' => $request->status]);

            if ($request->status === 'FOUND') {
                $notice->animal->update(['status' => 'ACTIVE']);

                AnimalHistory::create([
                    'animal_id'   => $notice->animal_id,
                    'user_id'     => Auth::id(),
                    'change_type' => 'STATUS',
                    'description' => 'Animal reportado como encontrado.',
                ]);
            }
        });

        return response()->json($notice->fresh());
    }
}