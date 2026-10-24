<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\CategoryResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * List all categories with count of published articles.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount(['articles as published_articles_count' => function ($q) {
            $q->where('status', 'published')
              ->whereNotNull('published_at')
              ->where('published_at', '<=', now());
        }])->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Show single category with its published articles.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $limit = min((int) $request->input('limit', 12), 30);

        $articles = Article::published()
            ->with(['category', 'author'])
            ->withCount('comments')
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate($limit);

        return response()->json([
            'category' => new CategoryResource($category),
            'articles' => ArticleResource::collection($articles)->response()->getData(true),
        ]);
    }

    /**
     * Admin/Editor: Store new category.
     */
    public function store(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, ['editor', 'admin'])) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Admin/Editor: Update category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!in_array($request->user()->role, ['editor', 'admin'])) {
            abort(403, 'Unauthorized.');
        }

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($validated['name'])) {
            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
        }

        if (array_key_exists('description', $validated)) {
            $category->description = $validated['description'];
        }

        $category->save();

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => new CategoryResource($category),
        ]);
    }

    /**
     * Admin: Delete category safely.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Only administrators can delete categories.');
        }

        $category = Category::findOrFail($id);

        if ($category->articles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category that contains articles. Reassign or delete the articles first.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
