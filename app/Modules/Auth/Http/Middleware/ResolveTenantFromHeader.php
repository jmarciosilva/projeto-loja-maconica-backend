<?php

namespace App\Modules\Auth\Http\Middleware;

use App\Modules\Administration\Models\Lodge;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerTenant = $request->header('X-Lodge-Uuid');
        $user = $request->user();
        $tenant = null;

        if ($headerTenant) {
            $tenant = Lodge::query()
                ->where('uuid', $headerTenant)
                ->where('status', 'active')
                ->first();

            if (! $tenant) {
                return response()->json(['message' => 'Invalid lodge context.'], 404);
            }

            if ($user && $user->lodge_id && $user->lodge_id !== $tenant->id) {
                return response()->json(['message' => 'Forbidden for this lodge context.'], 403);
            }
        }

        if (! $tenant && $user?->lodge) {
            $tenant = $user->lodge;
        }

        if ($tenant) {
            app()->instance('currentTenant', $tenant);
        }

        return $next($request);
    }
}
