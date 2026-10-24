<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Article $article): bool
    {
        if ($article->status === 'published' && $article->published_at && $article->published_at->lte(now())) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $user->id === $article->author_id || in_array($user->role, ['editor', 'admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['contributor', 'editor', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): bool
    {
        if (in_array($user->role, ['editor', 'admin'])) {
            return true;
        }

        if ($user->id === $article->author_id) {
            // Contributor can edit draft, rejected, or pending articles
            return in_array($article->status, ['draft', 'rejected', 'pending']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Article $article): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Contributors can only delete their own drafts or rejected articles
        if ($user->id === $article->author_id) {
            return in_array($article->status, ['draft', 'rejected']);
        }

        return false;
    }

    /**
     * Determine whether the user can submit the article for review.
     */
    public function submit(User $user, Article $article): bool
    {
        return $user->id === $article->author_id && in_array($article->status, ['draft', 'rejected']);
    }

    /**
     * Determine whether the user can publish articles.
     */
    public function publish(User $user, Article $article): bool
    {
        return in_array($user->role, ['editor', 'admin']);
    }

    /**
     * Determine whether the user can reject articles.
     */
    public function reject(User $user, Article $article): bool
    {
        return in_array($user->role, ['editor', 'admin']);
    }
}
