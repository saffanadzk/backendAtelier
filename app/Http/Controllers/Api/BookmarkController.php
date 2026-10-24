<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookmarkController extends Controller
{
    /**
     * List articles saved by the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $articles = Article::whereHas('bookmarks', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->published()
        ->with(['category', 'author'])
        ->withCount('comments')
        ->latest('published_at')
        ->paginate(15);

        return ArticleResource::collection($articles);
    }

    /**
     * Add bookmark for an article.
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $article = Article::published()->findOrFail($id);
        $user = $request->user();

        $bookmark = Bookmark::firstOrCreate([
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);

        return response()->json([
            'message' => 'Article saved to bookmarks.',
            'is_bookmarked' => true,
        ]);
    }

    /**
     * Remove bookmark for an article.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        Bookmark::where('user_id', $user->id)
            ->where('article_id', $id)
            ->delete();

        return response()->json([
            'message' => 'Article removed from bookmarks.',
            'is_bookmarked' => false,
        ]);
    }
}
