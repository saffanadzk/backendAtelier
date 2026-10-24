<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Get approved comments for a published article.
     */
    public function index(int $articleId): AnonymousResourceCollection
    {
        $comments = Comment::with('user')
            ->where('article_id', $articleId)
            ->approved()
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    /**
     * Store a comment on an article.
     */
    public function store(Request $request, int $articleId): JsonResponse
    {
        $article = Article::published()->findOrFail($articleId);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
            'content' => trim(strip_tags($validated['content'])),
            'status' => 'approved', // Auto-approved for verified readers, can be moderated
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment posted successfully.',
            'comment' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
