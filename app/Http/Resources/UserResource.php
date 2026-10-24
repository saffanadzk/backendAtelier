<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'avatar' => $this->avatar ? asset('storage/' . ltrim($this->avatar, '/')) : null,
            'bio' => $this->bio,
            'application_status' => $this->application_status ?? 'none',
            'application_field' => $this->application_field,
            'application_portfolio' => $this->application_portfolio,
            'application_pitch' => $this->application_pitch,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
