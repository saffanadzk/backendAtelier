<?php

use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\EditorialReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Editorial Endpoints
|--------------------------------------------------------------------------
*/
Route::get('/editorial-config', [ConfigController::class, 'show']);

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}/articles', [CategoryController::class, 'show']);

// Articles
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

// Article Comments (Public view)
Route::get('/articles/{id}/comments', [CommentController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated User Endpoints (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Current user profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::post('/me/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/me/apply-contributor', [AuthController::class, 'applyContributor']);

    // Bookmarks
    Route::get('/me/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/articles/{id}/bookmark', [BookmarkController::class, 'store']);
    Route::delete('/articles/{id}/bookmark', [BookmarkController::class, 'destroy']);

    // Comments (Creation and deletion)
    Route::post('/articles/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Contributor Article Management
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/my/articles', [ArticleController::class, 'myArticles']);
    Route::get('/my/articles/{id}', [ArticleController::class, 'myArticleShow']);
    Route::match(['patch', 'post'], '/my/articles/{id}', [ArticleController::class, 'update']);
    Route::post('/my/articles/{id}/update', [ArticleController::class, 'update']);
    Route::delete('/my/articles/{id}', [ArticleController::class, 'destroy']);
    Route::post('/my/articles/{id}/submit', [ArticleController::class, 'submitForReview']);

    // Editorial Review (Editor & Admin)
    Route::get('/editor/articles/pending', [EditorialReviewController::class, 'pendingArticles']);
    Route::match(['patch', 'post'], '/editor/articles/{id}/publish', [EditorialReviewController::class, 'publish']);
    Route::match(['patch', 'post'], '/editor/articles/{id}/reject', [EditorialReviewController::class, 'reject']);

    // Category Management (Editor & Admin)
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::match(['patch', 'put'], '/admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);

    // User Administration (Admin)
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::patch('/admin/users/{id}/role', [AdminUserController::class, 'updateRole']);
    Route::post('/admin/users/{id}/approve-application', [AdminUserController::class, 'approveApplication']);
    Route::post('/admin/users/{id}/reject-application', [AdminUserController::class, 'rejectApplication']);
});
