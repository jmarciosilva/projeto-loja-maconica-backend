<?php

namespace App\Modules\Administration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'degree' => $this->degree,
            'email' => $this->email,
            'status' => $this->status,
            'lodge' => new LodgeResource($this->whenLoaded('lodge')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($role) => [
                'uuid' => $role->uuid,
                'name' => $role->name,
                'slug' => $role->slug,
            ])->values()),
        ];
    }
}
