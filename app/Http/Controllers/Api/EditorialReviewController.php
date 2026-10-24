<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EditorialReviewController extends Controller
{
    /**
     * Editor / Admin: List all pending articles waiting for review.
     */
    public function pendingArticles(Request $request): AnonymousResourceCollection
    {
        $this->ensureEditorialStaff($request);

        $limit = min((int) $request->input('limit', 15), 50);

        $articles = Article::with(['category', 'author'])
            ->where('status', 'pending')
            ->latest('updated_at')
            ->paginate($limit);

        return ArticleResource::collection($articles);
    }

    /**
     * Editor / Admin: Approve and publish article.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $this->ensureEditorialStaff($request);

        $article = Article::with(['category', 'author'])->findOrFail($id);

        Gate::authorize('publish', $article);

        // Publication requirement: must have valid image
        if (empty($article->image)) {
            return response()->json([
                'message' => 'Article cannot be published without a valid featured image.',
            ], 422);
        }

        $article->status = 'published';
        $article->published_at = now();
        $article->rejection_note = null;
        $article->save();

        return response()->json([
            'message' => 'Article approved and published to the journal.',
            'article' => new ArticleResource($article),
        ]);
    }

    /**
     * Editor / Admin: Reject article with editorial revision notes.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $this->ensureEditorialStaff($request);

        $article = Article::with(['category', 'author'])->findOrFail($id);

        Gate::authorize('reject', $article);

        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $article->status = 'rejected';
        $article->rejection_note = $validated['rejection_note'];
        $article->save();

        return response()->json([
            'message' => 'Article rejected with editorial notes for the contributor.',
            'article' => new ArticleResource($article),
        ]);
    }

    private function ensureEditorialStaff(Request $request): void
    {
        if (!in_array($request->user()->role, ['editor', 'admin'])) {
            abort(403, 'Unauthorized. Editorial privileges required.');
        }
    }
}
