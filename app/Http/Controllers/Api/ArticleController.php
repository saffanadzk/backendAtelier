<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    /**
     * Public index: Returns published articles only.
     * Supports ?category={slug}, ?search={keyword}, ?limit={n}.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = min((int) $request->input('limit', 9), 30);

        $query = Article::query()
            ->with(['category', 'author'])
            ->withCount('comments')
            ->published()
            ->latest('published_at');

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Search in title, excerpt, or content
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('excerpt', 'like', $searchTerm)
                  ->orWhere('content', 'like', $searchTerm);
            });
        }

        $articles = $query->paginate($limit);

        return ArticleResource::collection($articles);
    }

    /**
     * Public show: Retrieve article by unique slug.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        $article = Article::with(['category', 'author'])
            ->withCount('comments')
            ->where('slug', $slug)
            ->firstOrFail();

        // Check view authorization (published vs private drafts)
        if ($article->status !== 'published') {
            if (!$user || ($user->id !== $article->author_id && !in_array($user->role, ['editor', 'admin']))) {
                abort(404, 'Article not found or not yet published.');
            }
        }

        // Retrieve related articles in the same category
        $related = Article::published()
            ->with(['category', 'author'])
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return response()->json([
            'article' => new ArticleResource($article),
            'related' => ArticleResource::collection($related),
        ]);
    }

    /**
     * Contributor / Author: List own articles.
     */
    public function myArticles(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $limit = min((int) $request->input('limit', 15), 50);

        $query = Article::query()
            ->with(['category', 'author'])
            ->where('author_id', $user->id)
            ->latest('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return ArticleResource::collection($query->paginate($limit));
    }

    /**
     * Contributor / Author: Show single own article.
     */
    public function myArticleShow(Request $request, int $id): JsonResponse
    {
        $article = Article::with(['category', 'author'])->findOrFail($id);

        Gate::authorize('view', $article);

        return response()->json([
            'article' => new ArticleResource($article),
        ]);
    }

    /**
     * Contributor / Staff: Create article with image upload.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Article::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'image_alt' => ['required', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'string', 'in:draft,submit'], // contributor intent
        ]);

        // Generate unique slug
        $slug = Article::generateUniqueSlug($validated['title']);

        // Upload image to local public storage
        $imagePath = $request->file('image')->store('articles', 'public');

        $status = ($validated['action'] ?? 'draft') === 'submit' ? 'pending' : 'draft';

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'author_id' => $request->user()->id, // Strictly authenticated user
            'image' => $imagePath,
            'image_alt' => $validated['image_alt'],
            'status' => $status,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'published_at' => null,
        ]);

        $article->load(['category', 'author']);

        return response()->json([
            'message' => $status === 'pending' ? 'Article submitted for review.' : 'Draft saved successfully.',
            'article' => new ArticleResource($article),
        ], 201);
    }

    /**
     * Contributor / Staff: Update article.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        Gate::authorize('update', $article);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'excerpt' => ['sometimes', 'required', 'string', 'max:1000'],
            'content' => ['sometimes', 'required', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_alt' => ['sometimes', 'required', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'string', 'in:draft,submit'],
        ]);

        // If title changed and article is draft/rejected, update slug
        if (isset($validated['title']) && $validated['title'] !== $article->title && in_array($article->status, ['draft', 'rejected'])) {
            $article->title = $validated['title'];
            $article->slug = Article::generateUniqueSlug($validated['title'], $article->id);
        }

        if (isset($validated['excerpt'])) {
            $article->excerpt = $validated['excerpt'];
        }

        if (isset($validated['content'])) {
            $article->content = $validated['content'];
        }

        if (isset($validated['category_id'])) {
            $article->category_id = $validated['category_id'];
        }

        if (isset($validated['image_alt'])) {
            $article->image_alt = $validated['image_alt'];
        }

        if (array_key_exists('seo_title', $validated)) {
            $article->seo_title = $validated['seo_title'];
        }

        if (array_key_exists('seo_description', $validated)) {
            $article->seo_description = $validated['seo_description'];
        }

        // Handle image replacement
        if ($request->hasFile('image')) {
            if ($article->image && !str_starts_with($article->image, 'http') && Storage::disk('public')->exists($article->image)) {
                Storage::disk('public')->delete($article->image);
            }
            $article->image = $request->file('image')->store('articles', 'public');
        }

        // Status transition if submitted
        if (($validated['action'] ?? null) === 'submit' && in_array($article->status, ['draft', 'rejected'])) {
            $article->status = 'pending';
            $article->rejection_note = null;
        }

        $article->save();
        $article->load(['category', 'author']);

        return response()->json([
            'message' => 'Article updated successfully.',
            'article' => new ArticleResource($article),
        ]);
    }

    /**
     * Contributor / Author: Submit draft to pending review.
     */
    public function submitForReview(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        Gate::authorize('submit', $article);

        $article->status = 'pending';
        $article->rejection_note = null;
        $article->save();

        return response()->json([
            'message' => 'Article submitted for editorial review.',
            'article' => new ArticleResource($article),
        ]);
    }

    /**
     * Contributor / Staff: Delete article.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        Gate::authorize('delete', $article);

        // Delete image file from storage if local
        if ($article->image && !str_starts_with($article->image, 'http') && Storage::disk('public')->exists($article->image)) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully.',
        ]);
    }
}
