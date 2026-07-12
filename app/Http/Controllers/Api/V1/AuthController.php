<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with(['lodge', 'roles'])
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'User is inactive.'], 403);
        }

        $plainTextToken = $user->createToken(
            $request->validated('device_name') ?? 'api',
            ['*'],
        )->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();

        AuditLog::query()->create([
            'lodge_id' => $user->lodge_id,
            'user_id' => $user->id,
            'action' => 'auth.login',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['lodge', 'roles']));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        AuditLog::query()->create([
            'lodge_id' => $request->user()->lodge_id,
            'user_id' => $request->user()->id,
            'action' => 'auth.logout',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        return response()->json(['message' => 'Logged out.']);
    }
}
