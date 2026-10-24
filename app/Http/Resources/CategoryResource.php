<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'articles_count' => $this->whenCounted('articles', $this->articles_count, fn() => $this->articles_count),
            'published_articles_count' => $this->when(
                isset($this->published_articles_count),
                $this->published_articles_count
            ),
        ];
    }
}
