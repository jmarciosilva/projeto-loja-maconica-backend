<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Http\Resources\UserResource;
use App\Modules\Administration\Models\AuditLog;
use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Services\LodgeProvisioningService;
use App\Modules\Auth\Http\Requests\RegisterLodgeRequest;
use App\Modules\Auth\Http\Requests\RegisterMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function __construct(private readonly LodgeProvisioningService $provisioning) {}

    /**
     * Self-service Lodge creation. The requester becomes the Lodge's
     * "Administrador Geral" and is signed in immediately.
     */
    public function registerLodge(RegisterLodgeRequest $request): JsonResponse
    {
        [$user, $token] = DB::transaction(function () use ($request) {
            $lodge = Lodge::query()->create([
                'name' => $request->validated('lodge_name'),
                'slug' => $request->validated('lodge_slug') ?? $this->uniqueSlug($request->validated('lodge_name')),
                'registration_number' => $request->validated('registration_number'),
                'email' => $request->validated('lodge_email'),
                'phone' => $request->validated('lodge_phone'),
                'potencia' => $request->validated('potencia'),
                'rito' => $request->validated('rito'),
                'type' => $request->validated('tipo'),
                'address_zip_code' => $request->validated('address_zip_code'),
                'address_street' => $request->validated('address_street'),
                'address_number' => $request->validated('address_number'),
                'address_complement' => $request->validated('address_complement'),
                'address_neighborhood' => $request->validated('address_neighborhood'),
                'address_city' => $request->validated('address_city'),
                'address_state' => $request->validated('address_state'),
                'referral_source' => $request->validated('referral_source'),
                'status' => 'active',
            ]);

            $roles = $this->provisioning->provisionDefaultRoles($lodge);

            $user = User::query()->create([
                'lodge_id' => $lodge->id,
                'name' => $request->validated('admin_name'),
                'nickname' => $request->validated('admin_nickname'),
                'cim' => $request->validated('admin_cim'),
                'cpf' => $request->validated('admin_cpf'),
                'degree' => $request->validated('admin_degree'),
                'whatsapp' => $request->validated('admin_whatsapp'),
                'email' => $request->validated('admin_email'),
                'password' => Hash::make($request->validated('admin_password')),
                'status' => 'active',
            ]);

            $user->roles()->attach($roles['admin']->id);

            $token = $user->createToken(
                $request->validated('device_name') ?? 'api',
                ['*'],
            )->plainTextToken;

            AuditLog::query()->create([
                'lodge_id' => $lodge->id,
                'user_id' => $user->id,
                'action' => 'auth.register_lodge',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
            ]);

            return [$user, $token];
        });

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user->load(['lodge', 'roles'])),
        ], 201);
    }

    /**
     * Self-service registration as a member (Obreiro) of an existing,
     * active Lodge. The account is created as "pending" and only gains
     * access once the Lodge approves it.
     */
    public function registerMember(RegisterMemberRequest $request): JsonResponse
    {
        $lodge = Lodge::query()
            ->where('uuid', $request->validated('lodge_uuid'))
            ->where('status', 'active')
            ->firstOrFail();

        $user = DB::transaction(function () use ($request, $lodge) {
            $roles = $this->provisioning->provisionDefaultRoles($lodge);

            $user = User::query()->create([
                'lodge_id' => $lodge->id,
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'status' => 'pending',
            ]);

            $user->roles()->attach($roles['member']->id);

            AuditLog::query()->create([
                'lodge_id' => $lodge->id,
                'user_id' => $user->id,
                'action' => 'auth.register_member',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
            ]);

            return $user;
        });

        return response()->json([
            'message' => 'Cadastro enviado. Aguarde a aprovação da Loja.',
            'user' => new UserResource($user->load(['lodge', 'roles'])),
        ], 201);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Lodge::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
