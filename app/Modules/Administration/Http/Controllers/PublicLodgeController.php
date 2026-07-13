<?php

namespace App\Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Http\Resources\PublicLodgeResource;
use App\Modules\Administration\Models\Lodge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicLodgeController extends Controller
{
    /**
     * Lists active Lodges for the member registration picker. Deliberately
     * unauthenticated: someone registering as Obreiro has no token yet.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));

        $lodges = Lodge::query()
            ->where('status', 'active')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return PublicLodgeResource::collection($lodges);
    }
}
