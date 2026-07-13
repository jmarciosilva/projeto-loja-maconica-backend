<?php

namespace App\Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Http\Resources\UserResource;
use App\Modules\Administration\Models\AuditLog;
use App\Modules\Administration\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberApprovalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeManagement($request);

        $members = User::query()
            ->where('lodge_id', $request->user()->lodge_id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(20);

        return UserResource::collection($members);
    }

    public function approve(Request $request, User $user): JsonResponse
    {
        $this->authorizeManagement($request);
        $this->ensureSameLodge($request, $user);

        $user->forceFill(['status' => 'active'])->save();

        $this->recordAudit($request, $user, 'members.approve');

        return response()->json(['data' => new UserResource($user)]);
    }

    public function reject(Request $request, User $user): JsonResponse
    {
        $this->authorizeManagement($request);
        $this->ensureSameLodge($request, $user);

        $user->forceFill(['status' => 'rejected'])->save();

        $this->recordAudit($request, $user, 'members.reject');

        return response()->json(['data' => new UserResource($user)]);
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless(
            $request->user()->hasPermission('users.manage'),
            403,
            'Você não tem permissão para gerenciar obreiros.',
        );
    }

    private function ensureSameLodge(Request $request, User $user): void
    {
        abort_unless(
            $user->lodge_id === $request->user()->lodge_id,
            403,
            'Este obreiro pertence a outra Loja.',
        );
    }

    private function recordAudit(Request $request, User $user, string $action): void
    {
        AuditLog::query()->create([
            'lodge_id' => $user->lodge_id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);
    }
}
