<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user('sanctum') ?? $request->user();
        $isAuthorOrStaff = $user && ($user->id === $this->author_id || in_array($user->role, ['editor', 'admin']));

        // Estimate reading time (approx 200 words per minute)
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'image_alt' => $this->image_alt,
            'status' => $this->status,
            'rejection_note' => $isAuthorOrStaff ? $this->rejection_note : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
                'avatar' => $this->author?->avatar ? asset('storage/' . ltrim($this->author->avatar, '/')) : null,
                'bio' => $this->author?->bio,
            ],
            'seo_title' => $this->seo_title ?: $this->title . ' | ATELIER Journal',
            'seo_description' => $this->seo_description ?: Str::limit(strip_tags($this->excerpt), 155),
            'reading_time' => "{$readingTime} min read",
            'published_at' => $this->published_at?->toIso8601String(),
            'published_at_formatted' => $this->published_at?->format('F d, Y'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'is_bookmarked' => $user ? $this->bookmarks()->where('user_id', $user->id)->exists() : false,
            'comments_count' => $this->whenCounted('comments', $this->comments_count, fn() => $this->comments_count),
        ];
    }
}
