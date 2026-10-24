<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->content,
            'status' => $this->status,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'avatar' => $this->user?->avatar ? asset('storage/' . ltrim($this->user->avatar, '/')) : null,
            ],
            'created_at' => $this->created_at?->diffForHumans(),
            'created_at_iso' => $this->created_at?->toIso8601String(),
        ];
    }
}
