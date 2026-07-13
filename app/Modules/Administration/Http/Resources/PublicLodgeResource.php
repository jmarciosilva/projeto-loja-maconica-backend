<?php

namespace App\Modules\Administration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal, non-sensitive Lodge representation for unauthenticated pickers
 * (e.g. the member registration flow choosing which Lodge to join).
 */
class PublicLodgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
